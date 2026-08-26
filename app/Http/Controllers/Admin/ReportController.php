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
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;


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

        // Only finished/completed requests for Accomplishment Reports preview
        $previewRequests = ServiceRequest::with(['category', 'client.user', 'project.histories', 'evaluation', 'latestHistory', 'histories'])
            ->where(function($q) {
                $q->whereHas('latestHistory', function($lh) {
                    $lh->where('current_status', 'Completed');
                })->orWhereHas('project.latestHistory', function($plh) {
                    $plh->where('current_status', 'Completed');
                });
            })
            ->orderBy('submitted_at', 'asc')
            ->orderBy('request_id', 'asc')
            ->get()
            ->map(function($req) {
                $startedDate = '';
                $completionDate = '';
                
                if ($req->project) {
                    $startHistory = $req->project->histories->where('current_status', 'In Progress')->first();
                    if ($startHistory) {
                        $startedDate = Carbon::parse($startHistory->updated_at)->format('n/j/Y');
                    }
                    $compHistory = $req->project->histories->where('current_status', 'Completed')->first();
                    if ($compHistory) {
                        $completionDate = Carbon::parse($compHistory->updated_at)->format('n/j/Y');
                    }
                }
                
                if (!$startedDate && $req->histories) {
                    $reqStart = $req->histories->where('current_status', 'In Progress')->first();
                    if ($reqStart) {
                        $startedDate = Carbon::parse($reqStart->updated_at)->format('n/j/Y');
                    }
                }

                if (!$completionDate && $req->histories) {
                    $reqComp = $req->histories->where('current_status', 'Completed')->first();
                    if ($reqComp) {
                        $completionDate = Carbon::parse($reqComp->updated_at)->format('n/j/Y');
                    }
                }

                if (!$startedDate && $req->submitted_at) {
                    $startedDate = Carbon::parse($req->submitted_at)->format('n/j/Y');
                }
                if (!$completionDate && $req->submitted_at) {
                    $completionDate = Carbon::parse($req->submitted_at)->format('n/j/Y');
                }

                $ratingVal = '';
                if ($req->evaluation) {
                    $r = (float) $req->evaluation->rating;
                    $ratingVal = ($r == (int)$r) ? (string)(int)$r : number_format($r, 1);
                }

                $catName = strtolower($req->category->category_name ?? '');
                $prefix = match(true) {
                    str_contains($catName, 'landscaping') => 'LS',
                    str_contains($catName, 'janitorial') => 'JS',
                    str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                    str_contains($catName, 'plumbing') => 'PLS',
                    str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                    str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
                    str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                    default => 'REQ'
                };

                return [
                    'request_id' => $req->request_id,
                    'category_id' => $req->category_id,
                    'category_name' => $req->category->category_name ?? 'General Maintenance',
                    'prefix' => $prefix,
                    'title' => $req->title,
                    'description' => $req->description,
                    'location' => $req->location ?? 'N/A',
                    'submitted_at' => $req->submitted_at ? Carbon::parse($req->submitted_at)->format('Y-m-d') : null,
                    'request_date_formatted' => $req->submitted_at ? Carbon::parse($req->submitted_at)->format('n/j/Y') : '',
                    'started_date' => $startedDate,
                    'completion_date' => $completionDate,
                    'rating' => $ratingVal,
                    'current_status' => 'Completed',
                ];
            });

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
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'period'      => 'nullable|string',
            'report_year' => 'nullable|integer'
        ]);

        $year = $request->filled('report_year') ? (int)$request->report_year : now()->year;
        $period = $request->input('period', 'sem1');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate   = Carbon::parse($request->end_date);
            $year      = $startDate->year;
        } elseif ($period === 'sem1') {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 6, 30)->endOfDay();
        } elseif ($period === 'sem2') {
            $startDate = Carbon::createFromDate($year, 7, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 12, 31)->endOfDay();
        } elseif ($period === 'year') {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 12, 31)->endOfDay();
        } else {
            $startDate = now()->month <= 6 ? Carbon::createFromDate($year, 1, 1)->startOfDay() : Carbon::createFromDate($year, 7, 1)->startOfDay();
            $endDate   = now()->month <= 6 ? Carbon::createFromDate($year, 6, 30)->endOfDay() : Carbon::createFromDate($year, 12, 31)->endOfDay();
        }

        $categoryId = $request->input('category_id');
        $category   = $categoryId ? Category::find($categoryId) : null;
        $categoryName = $category ? $category->category_name : 'ALL SERVICE UNITS';

        // Fetch ONLY finished/completed requests for Accomplishment Report
        $query = ServiceRequest::with(['category', 'project.histories', 'client.user', 'evaluation', 'latestHistory', 'histories'])
            ->where(function($q) {
                $q->whereHas('latestHistory', function($lh) {
                    $lh->where('current_status', 'Completed');
                })->orWhereHas('project.latestHistory', function($plh) {
                    $plh->where('current_status', 'Completed');
                });
            })
            ->whereBetween('submitted_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $serviceRequests = $query->orderBy('submitted_at', 'asc')->orderBy('request_id', 'asc')->get();

        // Audit Log
        UserLog::create([
            'user_id' => auth()->id(),
            'action' => "admin generated Accomplishment Report from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')} for {$categoryName}",
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Header (A to G columns)
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', "{$year} ACCOMPLISHMENT REPORT");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16)->setName('Times New Roman');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:G4');
        $sheet->setCellValue('A4', "MAINTENANCE SECTION: " . strtoupper($categoryName));
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11)->setName('Arial');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $startMonth = $startDate->month;
        $endMonth   = $endDate->month;
        if ($startMonth === 1 && $endMonth === 6) {
            $monthRange = 'JANUARY TO JUNE';
        } elseif ($startMonth === 7 && $endMonth === 12) {
            $monthRange = 'JULY TO DECEMBER';
        } elseif ($startMonth === 1 && $endMonth === 12) {
            $monthRange = 'JANUARY TO DECEMBER';
        } else {
            $startMonthStr = strtoupper($startDate->format('F'));
            $endMonthStr   = strtoupper($endDate->format('F'));
            $monthRange = ($startMonthStr === $endMonthStr) ? $startMonthStr : "{$startMonthStr} TO {$endMonthStr}";
        }

        $sheet->mergeCells('A6:G6');
        $sheet->setCellValue('A6', $monthRange);
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12)->setName('Arial');
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

        // 3. Populate Data - Sequential generation starting from 001
        $row = 10;
        $counter = 1;
        foreach ($serviceRequests as $req) {
            $catName = strtolower($req->category->category_name ?? '');
            $prefix = match(true) {
                str_contains($catName, 'landscaping') => 'LS',
                str_contains($catName, 'janitorial') => 'JS',
                str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                str_contains($catName, 'plumbing') => 'PLS',
                str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
                str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                default => 'REQ'
            };

            // Sequential numbering formatted with 3 digits (e.g. REQ-001, EMS-002, PLS-003)
            $reqNum = $prefix . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $counter++;

            $office = $req->location ?? 'N/A';
            $reqDate = $req->submitted_at ? Carbon::parse($req->submitted_at)->format('n/j/Y') : '';
            
            $startedDate = '';
            $completionDate = '';
            
            if ($req->project) {
                $startHistory = $req->project->histories->where('current_status', 'In Progress')->first();
                if ($startHistory) {
                    $startedDate = Carbon::parse($startHistory->updated_at)->format('n/j/Y');
                }
                
                $completedHistory = $req->project->histories->where('current_status', 'Completed')->first();
                if ($completedHistory) {
                    $completionDate = Carbon::parse($completedHistory->updated_at)->format('n/j/Y');
                }
            }

            if (!$startedDate && $req->histories) {
                $reqStart = $req->histories->where('current_status', 'In Progress')->first();
                if ($reqStart) {
                    $startedDate = Carbon::parse($reqStart->updated_at)->format('n/j/Y');
                }
            }

            if (!$completionDate && $req->histories) {
                $reqComp = $req->histories->where('current_status', 'Completed')->first();
                if ($reqComp) {
                    $completionDate = Carbon::parse($reqComp->updated_at)->format('n/j/Y');
                }
            }

            if (!$startedDate) {
                $startedDate = $reqDate;
            }
            if (!$completionDate) {
                $completionDate = $reqDate;
            }

            // Clientele Satisfaction Rating
            $ratingVal = '—';
            if ($req->evaluation) {
                $r = (float) $req->evaluation->rating;
                $ratingVal = ($r == (int)$r) ? (string)(int)$r : number_format($r, 1);
            }

            $taskDetails = $req->title;
            if ($req->description) {
                $taskDetails .= "\n" . $req->description;
            }

            $sheet->setCellValue('A'.$row, $reqNum);
            $sheet->setCellValue('B'.$row, $office);
            $sheet->setCellValue('C'.$row, $taskDetails);
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

        // Adjust column widths and A4 page setup
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(42);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(18);

        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Download Response
        $writer = new Xlsx($spreadsheet);

        $cleanCatName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $categoryName);
        $fileName = "{$year}_Accomplishment_Report_{$cleanCatName}_" . str_replace(' ', '_', $monthRange) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
