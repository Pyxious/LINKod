<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Project;
use App\Models\Worker;
use App\Models\Evaluation;
use App\Models\Category;
use App\Models\UserLog;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    public function index()
    {
        $totalRequests   = ServiceRequest::count();
        $totalProjects   = Project::count();
        $avgRating       = Evaluation::avg('rating');
        $availableWorkers= Worker::where('is_available', true)->count();

        $requestsByPriority = ServiceRequest::selectRaw('priority, count(*) as total')
            ->groupBy('priority')->pluck('total', 'priority');

        $requestsByCategory = ServiceRequest::join('category', 'request.category_id', '=', 'category.category_id')
            ->selectRaw('category.category_name, count(*) as total')
            ->groupBy('category.category_name')
            ->pluck('total', 'category_name');
            
        $categories = Category::all();
        $workers = Worker::with('user', 'team')->get();

        // Real database requests for Excel live preview (with evaluation)
        $previewRequests = ServiceRequest::with(['category', 'client.user', 'project.histories', 'evaluation'])
            ->latest('submitted_at')
            ->take(15)
            ->get();

        // Fetch real generated reports history from UserLog
        $recentReports = UserLog::with('user')
            ->where(function($q) {
                $q->where('action', 'LIKE', '%generated%')
                  ->orWhere('action', 'LIKE', '%report%');
            })
            ->latest('created_at')
            ->paginate(10);

        return view('admin.reports.index', compact(
            'totalRequests', 'totalProjects', 'avgRating',
            'availableWorkers', 'requestsByPriority', 'requestsByCategory',
            'categories', 'workers', 'previewRequests', 'recentReports'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:category,category_id',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date'
        ]);

        $categoryId = $request->input('category_id');
        $category   = $categoryId ? Category::find($categoryId) : null;
        $categoryName = $category ? $category->category_name : 'ALL MAINTENANCE SECTIONS';

        $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date) : now()->startOfYear();
        $endDate   = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date) : now();

        // Fetch requests for the report
        $query = ServiceRequest::with(['category', 'project.histories', 'client.user', 'evaluation'])
            ->whereBetween('submitted_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $serviceRequests = $query->get();

        // Audit Log
        UserLog::create([
            'user_id' => auth()->id(),
            'action' => "admin generated AR from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')} for {$categoryName}",
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Header (A to G columns)
        $year = $startDate->year;
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', "{$year} ACCOMPLISHMENT REPORT");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16)->setName('Times New Roman');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:G4');
        $sheet->setCellValue('A4', "MAINTENANCE SECTION: " . strtoupper($categoryName));
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);

        $startMonth = strtoupper($startDate->format('F'));
        $endMonth   = strtoupper($endDate->format('F'));
        $monthRange = ($startMonth === $endMonth) ? $startMonth : "{$startMonth} TO {$endMonth}";

        $sheet->mergeCells('A6:G6');
        $sheet->setCellValue('A6', $monthRange);
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Headers
        $sheet->mergeCells('A8:A9');
        $sheet->setCellValue('A8', "REQUISITION\nNUMBER");
        
        $sheet->mergeCells('B8:B9');
        $sheet->setCellValue('B8', "OFFICE/\nUNIT");
        
        $sheet->mergeCells('C8:C9');
        $sheet->setCellValue('C8', "TASK DETAILS");
        
        $sheet->mergeCells('D8:F8');
        $sheet->setCellValue('D8', 'DATES');

        $sheet->setCellValue('D9', 'REQUEST');
        $sheet->setCellValue('E9', 'STARTED');
        $sheet->setCellValue('F9', 'COMPLETION');

        $sheet->mergeCells('G8:G9');
        $sheet->setCellValue('G8', "CLIENTELE\nSATISFACTION\nRATING");
        
        $sheet->getStyle('A8:G9')->getFont()->setBold(true);
        $sheet->getStyle('A8:G9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:G9')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A8:G9')->getAlignment()->setWrapText(true);

        // Apply borders to headers
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A8:G9')->applyFromArray($styleArray);

        // 3. Populate Data
        $row = 10;
        foreach ($serviceRequests as $req) {
            $catName = strtolower($req->category->category_name ?? '');
            $prefix = match(true) {
                str_contains($catName, 'landscaping') => 'LS',
                str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                str_contains($catName, 'plumbing') => 'PS',
                str_contains($catName, 'painting') => 'PS',
                default => 'REQ'
            };

            $reqNum = $req->requisition_no ?: ($prefix . "-" . str_pad($req->request_id, 3, '0', STR_PAD_LEFT));
            $office = $req->location ?? 'N/A';
            $reqDate = \Carbon\Carbon::parse($req->submitted_at)->format('n/j/Y');
            
            $startedDate = '';
            $completionDate = '';
            
            if ($req->project) {
                $startHistory = \App\Models\ProjectHistory::where('project_id', $req->project->project_id)
                    ->where('current_status', 'In Progress')
                    ->first();
                if ($startHistory) {
                    $startedDate = \Carbon\Carbon::parse($startHistory->updated_at)->format('n/j/Y');
                }
                
                $completedHistory = \App\Models\ProjectHistory::where('project_id', $req->project->project_id)
                    ->where('current_status', 'Completed')
                    ->first();
                if ($completedHistory) {
                    $completionDate = \Carbon\Carbon::parse($completedHistory->updated_at)->format('n/j/Y');
                }
            }

            // Clientele Satisfaction Rating
            $ratingVal = '';
            if ($req->evaluation) {
                $r = (float) $req->evaluation->rating;
                $ratingVal = ($r == (int)$r) ? (string)(int)$r : number_format($r, 1);
            }

            $sheet->setCellValue('A'.$row, $reqNum);
            $sheet->setCellValue('B'.$row, $office);
            $sheet->setCellValue('C'.$row, $req->title . ($req->description ? "\n" . $req->description : ''));
            $sheet->setCellValue('D'.$row, $reqDate);
            $sheet->setCellValue('E'.$row, $startedDate);
            $sheet->setCellValue('F'.$row, $completionDate);
            $sheet->setCellValue('G'.$row, $ratingVal);
            
            $sheet->getStyle('A'.$row.':G'.$row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('A'.$row.':G'.$row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A'.$row.':B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$row.':G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A'.$row.':G'.$row)->applyFromArray($styleArray);
            
            $row++;
        }

        // Adjust column widths
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(18);

        // Download Response
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Accomplishment_Report_' . time() . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
