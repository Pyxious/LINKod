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
            'categories', 'recentReports'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:category,category_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $category = Category::findOrFail($request->category_id);
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);

        // Fetch requests for the report
        $serviceRequests = ServiceRequest::with('project.latestHistory')
            ->where('category_id', $category->category_id)
            ->whereBetween('submitted_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        // Audit Log
        UserLog::create([
            'user_id' => auth()->id(),
            'action' => "admin generated AR from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')} for {$category->category_name}",
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Header
        $year = $startDate->year;
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', "{$year} ACCOMPLISHMENT REPORT");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:E4');
        $sheet->setCellValue('A4', "MAINTENANCE SECTION: " . strtoupper($category->category_name));
        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->mergeCells('A6:E6');
        $sheet->setCellValue('A6', strtoupper($startDate->format('F')) . " TO " . strtoupper($endDate->format('F')));
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Headers
        $sheet->mergeCells('D8:E8');
        $sheet->setCellValue('D8', 'DATES');
        
        $headers = ['REQUISITION NUMBER', 'OFFICE/ UNIT', 'TASK DETAILS', 'REQUEST', 'STARTED', 'COMPLETION'];
        
        // Requisition Number
        $sheet->mergeCells('A8:A9');
        $sheet->setCellValue('A8', "REQUISITION\nNUMBER");
        
        // Office / Unit
        $sheet->mergeCells('B8:B9');
        $sheet->setCellValue('B8', "OFFICE/\nUNIT");
        
        // Task Details
        $sheet->mergeCells('C8:C9');
        $sheet->setCellValue('C8', "TASK DETAILS");
        
        // Dates (Sub headers)
        $sheet->setCellValue('D9', 'REQUEST');
        $sheet->setCellValue('E9', 'STARTED');
        $sheet->setCellValue('F9', 'COMPLETION'); // Wait, image has 5 columns (Req, Office, Task, Request, Started, Completion). That's 6 columns! D: Request, E: Started, F: Completion.
        
        // Let's adjust merges for 6 columns
        $sheet->unmergeCells('A2:E2');
        $sheet->mergeCells('A2:F2');
        
        $sheet->unmergeCells('A4:E4');
        $sheet->mergeCells('A4:F4');
        
        $sheet->unmergeCells('A6:E6');
        $sheet->mergeCells('A6:F6');
        
        $sheet->unmergeCells('D8:E8');
        $sheet->mergeCells('D8:F8');
        $sheet->setCellValue('D8', 'DATES');
        
        $sheet->getStyle('A8:F9')->getFont()->setBold(true);
        $sheet->getStyle('A8:F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:F9')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A8:F9')->getAlignment()->setWrapText(true);

        // Apply borders to headers
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A8:F9')->applyFromArray($styleArray);

        // Define Requisition Prefix
        $catName = strtolower($category->category_name);
        $prefix = match(true) {
            str_contains($catName, 'landscaping') => 'LS',
            str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
            str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
            str_contains($catName, 'plumbing') => 'PS',
            default => 'REQ'
        };

        // 3. Populate Data
        $row = 10;
        foreach ($serviceRequests as $req) {
            $reqNum = $prefix . "-" . str_pad($req->request_id, 3, '0', STR_PAD_LEFT);
            $office = strtok($req->location, '- ') ?: $req->location; // Rough extraction like BUCEILS
            
            $reqDate = \Carbon\Carbon::parse($req->submitted_at)->format('m/d/Y');
            
            // Try to find project dates
            $startedDate = '';
            $completionDate = '';
            
            if ($req->project) {
                // Find "In Progress" history for started date
                $startHistory = \App\Models\ProjectHistory::where('project_id', $req->project->project_id)
                    ->where('current_status', 'In Progress')
                    ->first();
                if ($startHistory) {
                    $startedDate = \Carbon\Carbon::parse($startHistory->updated_at)->format('m/d/Y');
                }
                
                // Find "Completed" history for completion date
                $completedHistory = \App\Models\ProjectHistory::where('project_id', $req->project->project_id)
                    ->where('current_status', 'Completed')
                    ->first();
                if ($completedHistory) {
                    $completionDate = \Carbon\Carbon::parse($completedHistory->updated_at)->format('m/d/Y');
                }
            }

            $sheet->setCellValue('A'.$row, $reqNum);
            $sheet->setCellValue('B'.$row, $office);
            $sheet->setCellValue('C'.$row, $req->title . "\n" . $req->description);
            $sheet->setCellValue('D'.$row, $reqDate);
            $sheet->setCellValue('E'.$row, $startedDate);
            $sheet->setCellValue('F'.$row, $completionDate);
            
            $sheet->getStyle('A'.$row.':F'.$row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('A'.$row.':F'.$row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A'.$row.':B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$row.':F'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray($styleArray);
            
            $row++;
        }

        // Adjust column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Download Response
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Accomplishment_Report_' . $category->category_name . '_' . time() . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
