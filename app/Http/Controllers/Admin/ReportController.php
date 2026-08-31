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
                    str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') || str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'CMS',
                    str_contains($catName, 'plumbing') => 'PLS',
                    str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
                    str_contains($catName, 'janitorial') => 'JS',
                    str_contains($catName, 'landscaping') => 'LS',
                    str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                    default => 'REQ'
                };

                $categoryOrder = match($prefix) {
                    'CMS' => 1,
                    'PLS' => 2,
                    'PAINT' => 3,
                    'JS' => 4,
                    'LS' => 5,
                    'MAN' => 6,
                    default => 7
                };

                return [
                    'request_id' => $req->request_id,
                    'category_id' => $req->category_id,
                    'category_name' => $req->category->category_name ?? 'General Maintenance',
                    'prefix' => $prefix,
                    'category_order' => $categoryOrder,
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
            })
            ->sort(function($a, $b) {
                if ($a['category_order'] !== $b['category_order']) {
                    return $a['category_order'] <=> $b['category_order'];
                }
                if ($a['submitted_at'] !== $b['submitted_at']) {
                    return strcmp($a['submitted_at'] ?? '', $b['submitted_at'] ?? '');
                }
                return $a['request_id'] <=> $b['request_id'];
            })
            ->values();

        // Fetch real generated reports history from UserLog
        $recentReports = UserLog::with('user')
            ->where(function($q) {
                $q->where('action', 'LIKE', '%generated%')
                  ->orWhere('action', 'LIKE', '%report%');
            })
            ->latest('created_at')
            ->paginate(10);

        // Map category ID to Team Leader info for live preview signatories
        $teamLeaders = \App\Models\Team::with('leader.staff.user', 'category')->get()->mapWithKeys(function($t) {
            $u = $t->leader?->staff?->user;
            $name = $u ? strtoupper(trim($u->first_name . ' ' . $u->last_name)) : 'TEAM LEADER';
            $secName = $t->category?->category_name ?? $t->team_name;
            return [$t->category_id => [
                'leader_name'  => $name,
                'section_name' => $secName,
            ]];
        });

        return view('admin.reports.index', compact(
            'totalRequests', 'totalProjects', 'avgRating',
            'availableWorkers', 'requestsByPriority', 'requestsByCategory',
            'categories', 'workers', 'previewRequests', 'recentReports', 'teamLeaders'
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

        $serviceRequests = $query->get()
            ->sort(function($a, $b) {
                $catA = strtolower($a->category->category_name ?? '');
                $catB = strtolower($b->category->category_name ?? '');

                $orderA = match(true) {
                    str_contains($catA, 'carpentry') || str_contains($catA, 'masonry') || str_contains($catA, 'electrical') || str_contains($catA, 'mechanical') => 1,
                    str_contains($catA, 'plumbing') => 2,
                    str_contains($catA, 'painting') || str_contains($catA, 'paint') => 3,
                    str_contains($catA, 'janitorial') => 4,
                    str_contains($catA, 'landscaping') => 5,
                    str_contains($catA, 'manpower') || str_contains($catA, 'event') => 6,
                    default => 7
                };

                $orderB = match(true) {
                    str_contains($catB, 'carpentry') || str_contains($catB, 'masonry') || str_contains($catB, 'electrical') || str_contains($catB, 'mechanical') => 1,
                    str_contains($catB, 'plumbing') => 2,
                    str_contains($catB, 'painting') || str_contains($catB, 'paint') => 3,
                    str_contains($catB, 'janitorial') => 4,
                    str_contains($catB, 'landscaping') => 5,
                    str_contains($catB, 'manpower') || str_contains($catB, 'event') => 6,
                    default => 7
                };

                if ($orderA !== $orderB) {
                    return $orderA <=> $orderB;
                }

                $dateA = $a->submitted_at ? $a->submitted_at->timestamp : 0;
                $dateB = $b->submitted_at ? $b->submitted_at->timestamp : 0;
                if ($dateA !== $dateB) {
                    return $dateA <=> $dateB;
                }

                return $a->request_id <=> $b->request_id;
            })
            ->values();

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
                str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') || str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'CMS',
                str_contains($catName, 'plumbing') => 'PLS',
                str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
                str_contains($catName, 'janitorial') => 'JS',
                str_contains($catName, 'landscaping') => 'LS',
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

        // 4. Signatures Section (Prepared By, Certified True and Correct, Noted By)
        $teamLeaderName = 'GSO MAINTENANCE TEAM LEADERS';
        $teamSectionName = 'General Services Office';
        if ($categoryId) {
            $team = \App\Models\Team::where('category_id', $categoryId)->with('leader.staff.user')->first();
            if ($team && $team->leader?->staff?->user) {
                $u = $team->leader->staff->user;
                $teamLeaderName = strtoupper(trim($u->first_name . ' ' . $u->last_name));
                $teamSectionName = $category ? $category->category_name : $team->team_name;
            }
        }

        $sigRow = $row + 2;

        // Prepared By:
        $sheet->setCellValue('A' . $sigRow, "Prepared By:");
        $sheet->getStyle('A' . $sigRow)->getFont()->setSize(10)->setName('Arial');

        $sheet->setCellValue('A' . ($sigRow + 3), $teamLeaderName);
        $sheet->getStyle('A' . ($sigRow + 3))->getFont()->setBold(true)->setSize(11)->setName('Arial');

        $sheet->setCellValue('A' . ($sigRow + 4), "Team Leader");
        $sheet->getStyle('A' . ($sigRow + 4))->getFont()->setSize(10)->setName('Arial');

        $sheet->setCellValue('A' . ($sigRow + 5), $teamSectionName);
        $sheet->getStyle('A' . ($sigRow + 5))->getFont()->setSize(10)->setName('Arial');

        // Certified True and Correct:
        $certRow = $sigRow + 7;
        $sheet->setCellValue('A' . $certRow, "Certified True and Correct:");
        $sheet->getStyle('A' . $certRow)->getFont()->setSize(10)->setName('Arial');

        $sheet->setCellValue('A' . ($certRow + 3), "REY A. PADILLA");
        $sheet->getStyle('A' . ($certRow + 3))->getFont()->setBold(true)->setSize(11)->setName('Arial');

        $sheet->setCellValue('A' . ($certRow + 4), "Administrative Officer I");
        $sheet->getStyle('A' . ($certRow + 4))->getFont()->setSize(10)->setName('Arial');

        $sheet->setCellValue('A' . ($certRow + 5), "Head, General Services Office");
        $sheet->getStyle('A' . ($certRow + 5))->getFont()->setSize(10)->setName('Arial');

        // Noted By:
        $notedRow = $certRow + 7;
        $sheet->setCellValue('A' . $notedRow, "Noted By:");
        $sheet->getStyle('A' . $notedRow)->getFont()->setSize(10)->setName('Arial');

        $sheet->setCellValue('A' . ($notedRow + 3), "MA. MYRA A. CAPARAS");
        $sheet->getStyle('A' . ($notedRow + 3))->getFont()->setBold(true)->setSize(11)->setName('Arial');

        $sheet->setCellValue('A' . ($notedRow + 4), "Acting Chief Administrative Officer for");
        $sheet->getStyle('A' . ($notedRow + 4))->getFont()->setSize(10)->setName('Arial');

        $sheet->setCellValue('A' . ($notedRow + 5), "Administrative Services Division");
        $sheet->getStyle('A' . ($notedRow + 5))->getFont()->setSize(10)->setName('Arial');

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

        // Download Response via clean binary stream
        $cleanCatName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $categoryName);
        $fileName = "{$year}_Accomplishment_Report_{$cleanCatName}_" . str_replace(' ', '_', $monthRange) . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'linkod_report_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
