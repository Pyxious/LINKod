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
use Illuminate\Support\Facades\Cache;
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
        // Cache summary stats (600s — 10 min)
        $summaryStats = Cache::remember('admin_reports_summary', 600, function () {
            return [
                'totalRequests'       => ServiceRequest::count(),
                'totalProjects'       => Project::count(),
                'avgRating'           => Evaluation::avg('rating'),
                'availableWorkers'    => Worker::where('is_available', true)->count(),
                'requestsByPriority'  => ServiceRequest::selectRaw('priority, count(*) as total')->groupBy('priority')->pluck('total', 'priority'),
                'requestsByCategory'  => ServiceRequest::join('category', 'request.category_id', '=', 'category.category_id')
                    ->selectRaw('category.category_name, count(*) as total')
                    ->groupBy('category.category_name')
                    ->pluck('total', 'category_name'),
            ];
        });
        extract($summaryStats);

        $categories = Category::all();
        $workers    = Worker::with(['staff.user', 'team.category'])->get()->map(function($w) {
            $name = trim(($w->staff?->user?->first_name ?? '') . ' ' . ($w->staff?->user?->last_name ?? ''));
            $service = $w->team?->team_name ?? $w->team?->category?->category_name ?? 'General Maintenance';
            return [
                'worker_id' => $w->worker_id,
                'name'      => $name ?: 'Worker #' . $w->worker_id,
                'service'   => $service,
            ];
        })->sortBy('name')->values();

        $previewRequests = ServiceRequest::with(['category', 'client.user', 'project.histories', 'project.workers.staff.user', 'project.workers.team.category', 'evaluation', 'latestHistory', 'histories'])
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
                        if ($startHistory) $startedDate = Carbon::parse($startHistory->updated_at)->format('n/j/Y');
                        $compHistory = $req->project->histories->where('current_status', 'Completed')->first();
                        if ($compHistory) $completionDate = Carbon::parse($compHistory->updated_at)->format('n/j/Y');
                    }

                    if (!$startedDate && $req->histories) {
                        $reqStart = $req->histories->where('current_status', 'In Progress')->first();
                        if ($reqStart) $startedDate = Carbon::parse($reqStart->updated_at)->format('n/j/Y');
                    }
                    if (!$completionDate && $req->histories) {
                        $reqComp = $req->histories->where('current_status', 'Completed')->first();
                        if ($reqComp) $completionDate = Carbon::parse($reqComp->updated_at)->format('n/j/Y');
                    }
                    if (!$startedDate && $req->submitted_at)    $startedDate    = Carbon::parse($req->submitted_at)->format('n/j/Y');
                    if (!$completionDate && $req->submitted_at) $completionDate = Carbon::parse($req->submitted_at)->format('n/j/Y');

                    $ratingVal = '';
                    if ($req->evaluation) {
                        $r = (float) $req->evaluation->rating;
                        $ratingVal = ($r == (int)$r) ? (string)(int)$r : number_format($r, 1);
                    }

                    $catName = strtolower($req->category->category_name ?? '');
                    $prefix = match(true) {
                        str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') || str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'CMS',
                        str_contains($catName, 'plumbing') => 'PLS',
                        str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAS',
                        str_contains($catName, 'janitorial') => 'JS',
                        str_contains($catName, 'landscaping') => 'LS',
                        str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                        default => 'REQ'
                    };
                    $categoryOrder = match($prefix) { 'CMS' => 1, 'PLS' => 2, 'PAS', 'PAINT' => 3, 'JS' => 4, 'LS' => 5, 'MAN' => 6, default => 7 };
                    $isManpower = $prefix === 'MAN' || str_contains($catName, 'manpower') || str_contains($catName, 'event');
                    $verifiedWork = $req->project?->nature_of_work;
                    $hasVerifiedWork = $verifiedWork && !in_array(trim($verifiedWork), ['Completed', 'Repair & Maintenance Done', 'Direct Repair', '']);

                    if ($isManpower) {
                        $taskTitle = $hasVerifiedWork ? $verifiedWork : $req->title;
                        $taskDesc  = null;
                    } else {
                        $taskTitle = $req->title;
                        $taskDesc  = ($hasVerifiedWork && $verifiedWork !== $req->title) ? $verifiedWork : ($req->display_description ?? null);
                    }

                    $projectWorkers = $req->project?->workers ?? collect();
                    $workerNames = $projectWorkers->map(function($w) {
                        return trim(($w->staff?->user?->first_name ?? '') . ' ' . ($w->staff?->user?->last_name ?? ''));
                    })->filter()->values();
                    $workerIds = $projectWorkers->pluck('worker_id')->values()->all();
                    $workerDetails = $projectWorkers->map(function($w) use ($req) {
                        $wName = trim(($w->staff?->user?->first_name ?? '') . ' ' . ($w->staff?->user?->last_name ?? ''));
                        $wService = $w->team?->team_name ?? $w->team?->category?->category_name ?? $req->category?->category_name ?? 'General Maintenance';
                        return [
                            'worker_id' => $w->worker_id,
                            'name'      => $wName ?: 'Worker #' . $w->worker_id,
                            'service'   => $wService,
                        ];
                    })->values()->all();

                    return [
                        'request_id'             => $req->request_id,
                        'category_id'            => $req->category_id,
                        'category_name'          => $req->category->category_name ?? 'General Maintenance',
                        'prefix'                 => $prefix,
                        'section_name'           => $this->classifySection($req),
                        'category_order'         => $categoryOrder,
                        'title'                  => $taskTitle,
                        'description'            => $taskDesc,
                        'location'               => $req->location ?? 'N/A',
                        'submitted_at'           => $req->submitted_at ? Carbon::parse($req->submitted_at)->format('Y-m-d') : null,
                        'request_date_formatted' => $req->submitted_at ? Carbon::parse($req->submitted_at)->format('n/j/Y') : '',
                        'started_date'           => $startedDate,
                        'completion_date'        => $completionDate,
                        'rating'                 => $ratingVal,
                        'function_ratings'       => $req->evaluation ? $req->evaluation->function_ratings : null,
                        'current_status'         => 'Completed',
                        'assigned_workers'       => $workerNames->join(', ') ?: 'Unassigned',
                        'worker_names'           => $workerNames->all(),
                        'worker_ids'             => $workerIds,
                        'worker_details'         => $workerDetails,
                    ];
                })
                ->sort(function($a, $b) {
                    if ($a['category_order'] !== $b['category_order']) return $a['category_order'] <=> $b['category_order'];
                    if ($a['submitted_at'] !== $b['submitted_at']) return strcmp($a['submitted_at'] ?? '', $b['submitted_at'] ?? '');
                    return $a['request_id'] <=> $b['request_id'];
                })
                ->values();

        // Recent report audit log (not cached — must be live)
        $recentReports = UserLog::with('user')
            ->where(function($q) {
                $q->where('action', 'LIKE', '%generated%')
                  ->orWhere('action', 'LIKE', '%report%');
            })
            ->latest('created_at')
            ->paginate(10);

        // Cache team leaders for signatories (600s)
        $teamLeaders = Cache::remember('admin_reports_team_leaders', 600, function () {
            return \App\Models\Team::with('leader.staff.user', 'category')->get()->mapWithKeys(function($t) {
                $u = $t->leader?->staff?->user;
                $name = $u ? strtoupper(trim($u->first_name . ' ' . $u->last_name)) : 'TEAM LEADER';
                $secName = $t->category?->category_name ?? $t->team_name;
                return [$t->category_id => ['leader_name' => $name, 'section_name' => $secName]];
            });
        });

        [$defaultStart, $defaultEnd, $defaultYear, $defaultPeriodText] = $this->resolveDateRange(request());
        $summarySections = $this->getSectionData($defaultStart, $defaultEnd);

        return view('admin.reports.index', compact(
            'totalRequests', 'totalProjects', 'avgRating',
            'availableWorkers', 'requestsByPriority', 'requestsByCategory',
            'categories', 'workers', 'previewRequests', 'recentReports', 'teamLeaders',
            'summarySections'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'report_type'    => 'nullable|string',
            'category_id'    => 'nullable|exists:category,category_id',
            'worker_id'      => 'nullable|exists:worker,worker_id',
            'include_worker' => 'nullable',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'period'         => 'nullable|string',
            'report_year'    => 'nullable|integer'
        ]);

        if ($request->input('report_type') === 'Summary of Accomplishment & Clientele Satisfaction Survey') {
            return $this->exportSummarySurvey($request);
        }

        [$startDate, $endDate, $year, $periodText, $monthRange] = $this->resolveDateRange($request);

        $categoryId    = $request->input('category_id');
        $workerId      = $request->input('worker_id');
        $includeWorker = $request->boolean('include_worker');
        $category      = $categoryId ? Category::find($categoryId) : null;
        $categoryName  = $category ? $category->category_name : 'ALL SERVICE UNITS';

        // Fetch ONLY finished/completed requests for Accomplishment Report
        $query = ServiceRequest::with([
            'category',
            'project.histories',
            'project.workers.staff.user',
            'project.workers.team.category',
            'client.user',
            'evaluation',
            'latestHistory',
            'histories'
        ])
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

        if ($workerId) {
            $query->whereHas('project.workers', function($wq) use ($workerId) {
                $wq->where('project_worker.worker_id', $workerId);
            });
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
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();

        $endCol = $includeWorker ? 'H' : 'G';

        // 1. Header (A to G/H columns)
        $sheet->mergeCells("A2:{$endCol}2");
        $sheet->setCellValue('A2', "{$year} ACCOMPLISHMENT REPORT");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14)->setName('Times New Roman');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A4:{$endCol}4");
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

        $sheet->mergeCells("A6:{$endCol}6");
        $sheet->setCellValue('A6', $monthRange);
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(11)->setName('Arial');
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
        $sheet->setCellValue('G8', "CLIENTELE\nSATISFACTION");

        if ($includeWorker) {
            $sheet->mergeCells('H8:H9');
            $sheet->setCellValue('H8', "WORKER\nASSIGNED");
        }
        
        $headerRange = "A8:{$endCol}9";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10)->setName('Arial');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);

        // Apply borders to headers
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle($headerRange)->applyFromArray($styleArray);

        // 3. Populate Data - Sequential generation starting from 001
        $row = 10;
        $counter = 1;
        foreach ($serviceRequests as $req) {
            $catName = strtolower($req->category->category_name ?? '');
            $prefix = match(true) {
                str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') || str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'CMS',
                str_contains($catName, 'plumbing') => 'PLS',
                str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAS',
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

            $isManpower = $prefix === 'MAN' || str_contains($catName, 'manpower') || str_contains($catName, 'event');
            $verifiedWork = $req->project?->nature_of_work;
            $hasVerifiedWork = $verifiedWork && !in_array(trim($verifiedWork), ['Completed', 'Repair & Maintenance Done', 'Direct Repair', '']);

            if ($isManpower) {
                $taskDetails = $hasVerifiedWork ? $verifiedWork : $req->title;
            } else {
                $taskDetails = $req->title;
                if ($hasVerifiedWork && $verifiedWork !== $req->title) {
                    $taskDetails .= "\n" . $verifiedWork;
                } elseif ($req->display_description) {
                    $taskDetails .= "\n" . $req->display_description;
                }
            }

            $projectWorkers = $req->project?->workers ?? collect();
            $workerNames = $projectWorkers->map(function($w) {
                return trim(($w->staff?->user?->first_name ?? '') . ' ' . ($w->staff?->user?->last_name ?? ''));
            })->filter()->values();
            $workerNamesStr = $workerNames->join(', ') ?: 'Unassigned';

            $sheet->setCellValue('A'.$row, $reqNum);
            $sheet->setCellValue('B'.$row, $office);
            $sheet->setCellValue('C'.$row, $taskDetails);
            $sheet->setCellValue('D'.$row, $reqDate);
            $sheet->setCellValue('E'.$row, $startedDate);
            $sheet->setCellValue('F'.$row, $completionDate);
            $sheet->setCellValue('G'.$row, $ratingVal);
            if ($includeWorker) {
                $sheet->setCellValue('H'.$row, $workerNamesStr);
            }
            
            $rowRange = "A{$row}:{$endCol}{$row}";
            $sheet->getStyle($rowRange)->getFont()->setSize(10)->setName('Arial');
            $sheet->getStyle($rowRange)->getAlignment()->setWrapText(true);
            $sheet->getStyle($rowRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A'.$row.':B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D'.$row.':G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if ($includeWorker) {
                $sheet->getStyle('H'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $sheet->getStyle($rowRange)->applyFromArray($styleArray);
            
            $row++;
        }

        $sigRow = $row + 2;

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

        // Adjust column widths for clean Portrait A4 Print layout
        $sheet->getColumnDimension('A')->setWidth(14.5);
        $sheet->getColumnDimension('B')->setWidth(17.5);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(12.5);
        $sheet->getColumnDimension('E')->setWidth(12.5);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        if ($includeWorker) {
            $sheet->getColumnDimension('H')->setWidth(20);
        }

        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
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

    public function printSummary(Request $request)
    {
        $reportType = $request->input('report_type', 'Accomplishment Report');
        if ($reportType === 'Accomplishment Report') {
            return $this->printAccomplishment($request);
        }

        $request->validate([
            'category_id' => 'nullable|exists:category,category_id',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'period'      => 'nullable|string',
            'report_year' => 'nullable|integer'
        ]);

        [$startDate, $endDate, $year, $periodText, $monthRange] = $this->resolveDateRange($request);
        $categoryId = $request->input('category_id');

        $sections = $this->getSectionData($startDate, $endDate, $categoryId);
        $sectionsWithSurvey = array_filter($sections, fn($s) => !empty($s['raters']));
        if (empty($sectionsWithSurvey)) {
            $sectionsWithSurvey = $sections;
        }

        return view('admin.reports.print-summary', compact(
            'sections',
            'sectionsWithSurvey',
            'periodText',
            'year'
        ));
    }

    public function printAccomplishment(Request $request)
    {
        [$startDate, $endDate, $year, $periodText, $monthRange] = $this->resolveDateRange($request);
        $categoryId    = $request->input('category_id');
        $workerId      = $request->input('worker_id');
        $includeWorker = $request->boolean('include_worker');

        $category     = $categoryId ? Category::find($categoryId) : null;
        $categoryName = $category ? $category->category_name : 'ALL SERVICE UNITS';

        $query = ServiceRequest::with([
            'category',
            'project.histories',
            'project.workers.staff.user',
            'project.workers.team.category',
            'client.user',
            'evaluation',
            'latestHistory',
            'histories'
        ])
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

        if ($workerId) {
            $query->whereHas('project.workers', function($wq) use ($workerId) {
                $wq->where('project_worker.worker_id', $workerId);
            });
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

        return view('admin.reports.print-accomplishment', compact(
            'year',
            'categoryName',
            'monthRange',
            'includeWorker',
            'serviceRequests',
            'teamLeaderName',
            'teamSectionName'
        ));
    }

    public function exportSummarySurvey(Request $request)
    {
        [$startDate, $endDate, $year, $periodText, $monthRange] = $this->resolveDateRange($request);
        $categoryId = $request->input('category_id');
        $category   = $categoryId ? Category::find($categoryId) : null;
        $categoryName = $category ? $category->category_name : 'ALL SERVICE UNITS';

        $sections = $this->getSectionData($startDate, $endDate, $categoryId);
        $sectionsWithSurvey = array_filter($sections, fn($s) => !empty($s['raters']));
        if (empty($sectionsWithSurvey)) {
            $sectionsWithSurvey = $sections;
        }

        // Audit Log
        UserLog::create([
            'user_id' => auth()->id(),
            'action' => "admin generated Summary & Satisfaction Survey Report from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')} for {$categoryName}",
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // ==========================================
        // SHEET 1: Summary Report (Photo 1)
        // ==========================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Summary Report');

        $sheet1->mergeCells('A2:B2');
        $sheet1->setCellValue('A2', 'SUMMARY OF ACCOMPLISHMENT REPORT AND CLIENTELE SATISFACTION SURVEY');
        $sheet1->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setName('Times New Roman');
        $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet1->mergeCells('A3:B3');
        $sheet1->setCellValue('A3', strtoupper($periodText));
        $sheet1->getStyle('A3')->getFont()->setBold(true)->setSize(11)->setName('Times New Roman');
        $sheet1->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        foreach ($sections as $sec) {
            $sheet1->mergeCells("A{$row}:B{$row}");
            $sheet1->setCellValue("A{$row}", 'Maintenance Section: ' . $sec['name']);
            $sheet1->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
            $sheet1->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');

            $row1 = $row + 1;
            $sheet1->setCellValue("A{$row1}", 'Total Number of Request Received:');
            $sheet1->setCellValue("B{$row1}", $sec['total_requests']);
            $sheet1->getStyle("B{$row1}")->getFont()->setBold(true);
            $sheet1->getStyle("B{$row1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row2 = $row + 2;
            $sheet1->setCellValue("A{$row2}", 'Clientele Satisfaction Survey Result:');
            $csVal = $sec['cs_result'] > 0 ? number_format($sec['cs_result'], 2) : '0';
            $sheet1->setCellValue("B{$row2}", $csVal);
            $sheet1->getStyle("B{$row2}")->getFont()->setBold(true);
            $sheet1->getStyle("B{$row2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet1->getStyle("A{$row}:B{$row2}")->applyFromArray($thinBorder);
            $row += 4;
        }

        $sigRow = $row + 1;
        $sheet1->setCellValue("A{$sigRow}", "Prepared by:");
        $sheet1->setCellValue("B{$sigRow}", "Certified Correct:");

        $nameRow = $sigRow + 3;
        $sheet1->setCellValue("A{$nameRow}", "REY A. PADILLA");
        $sheet1->setCellValue("B{$nameRow}", "MA. MYRA A. CAPARAS");
        $sheet1->getStyle("A{$nameRow}:B{$nameRow}")->getFont()->setBold(true)->setUnderline(true);

        $titleRow1 = $nameRow + 1;
        $sheet1->setCellValue("A{$titleRow1}", "Administrative Officer II");
        $sheet1->setCellValue("B{$titleRow1}", "Acting Chief Administrative Officer");

        $titleRow2 = $nameRow + 2;
        $sheet1->setCellValue("A{$titleRow2}", "Head, General Services Office");
        $sheet1->setCellValue("B{$titleRow2}", "For Administrative Services Division");

        $sheet1->getColumnDimension('A')->setWidth(48);
        $sheet1->getColumnDimension('B')->setWidth(25);
        $sheet1->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet1->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet1->getPageSetup()->setFitToWidth(1);
        $sheet1->getPageSetup()->setFitToHeight(0);

        // ==========================================
        // SHEET 2: Satisfaction Survey (Photo 2)
        // ==========================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Satisfaction Survey');

        $s2Row = 2;
        foreach ($sectionsWithSurvey as $sec) {
            $sheet2->mergeCells("A{$s2Row}:F{$s2Row}");
            $sheet2->setCellValue("A{$s2Row}", "CLIENTELE SATISFACTION SURVEY");
            $sheet2->getStyle("A{$s2Row}")->getFont()->setBold(true)->setSize(12)->setName('Times New Roman');
            $sheet2->getStyle("A{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $s2Row++;
            $sheet2->mergeCells("A{$s2Row}:F{$s2Row}");
            $sheet2->setCellValue("A{$s2Row}", "Maintenance Section: " . $sec['name']);
            $sheet2->getStyle("A{$s2Row}")->getFont()->setBold(true)->setSize(11);
            $sheet2->getStyle("A{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $s2Row++;
            $sheet2->mergeCells("A{$s2Row}:F{$s2Row}");
            $sheet2->setCellValue("A{$s2Row}", strtoupper($periodText));
            $sheet2->getStyle("A{$s2Row}")->getFont()->setBold(true)->setSize(10);
            $sheet2->getStyle("A{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $s2Row += 2;
            $tableStart = $s2Row;
            $h1 = $s2Row;
            $h2 = $s2Row + 1;
            $sheet2->mergeCells("A{$h1}:A{$h2}");
            $sheet2->setCellValue("A{$h1}", "Number of\nRater");

            $sheet2->mergeCells("B{$h1}:F{$h1}");
            $sheet2->setCellValue("B{$h1}", "Functions");

            $sheet2->setCellValue("B{$h2}", "Quality of\nService");
            $sheet2->setCellValue("C{$h2}", "Attitude");
            $sheet2->setCellValue("D{$h2}", "Safety\nPrecautions\nAwareness");
            $sheet2->setCellValue("E{$h2}", "Time\nBounded");
            $sheet2->setCellValue("F{$h2}", "Workplace\nHousekeeping");

            $sheet2->getStyle("A{$h1}:F{$h2}")->getFont()->setBold(true)->setSize(9.5);
            $sheet2->getStyle("A{$h1}:F{$h2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("A{$h1}:F{$h2}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet2->getStyle("A{$h1}:F{$h2}")->getAlignment()->setWrapText(true);

            $s2Row += 2;
            if (empty($sec['raters'])) {
                $sheet2->mergeCells("A{$s2Row}:F{$s2Row}");
                $sheet2->setCellValue("A{$s2Row}", "No survey responses recorded for this section.");
                $sheet2->getStyle("A{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle("A{$s2Row}")->getFont()->setItalic(true);
                $sheet2->getStyle("A{$tableStart}:F{$s2Row}")->applyFromArray($thinBorder);
                $s2Row += 3;
            } else {
                foreach ($sec['raters'] as $r) {
                    $sheet2->setCellValue("A{$s2Row}", $r['rater_no']);
                    $sheet2->setCellValue("B{$s2Row}", $r['quality']);
                    $sheet2->setCellValue("C{$s2Row}", $r['attitude']);
                    $sheet2->setCellValue("D{$s2Row}", $r['safety']);
                    $sheet2->setCellValue("E{$s2Row}", $r['time']);
                    $sheet2->setCellValue("F{$s2Row}", $r['housekeeping']);
                    $sheet2->getStyle("A{$s2Row}:F{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet2->getStyle("A{$s2Row}")->getFont()->setBold(true);
                    $s2Row++;
                }
                $sheet2->getStyle("A{$tableStart}:F" . ($s2Row - 1))->applyFromArray($thinBorder);

                // Counts
                $s2Row += 1;
                foreach ([5, 4] as $score) {
                    $sheet2->setCellValue("A{$s2Row}", $score);
                    $sheet2->setCellValue("B{$s2Row}", $sec['counts'][$score]['quality'] ?? 0);
                    $sheet2->setCellValue("C{$s2Row}", $sec['counts'][$score]['attitude'] ?? 0);
                    $sheet2->setCellValue("D{$s2Row}", $sec['counts'][$score]['safety'] ?? 0);
                    $sheet2->setCellValue("E{$s2Row}", $sec['counts'][$score]['time'] ?? 0);
                    $sheet2->setCellValue("F{$s2Row}", $sec['counts'][$score]['housekeeping'] ?? 0);
                    $sheet2->getStyle("A{$s2Row}:F{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet2->getStyle("A{$s2Row}")->getFont()->setBold(true);
                    $s2Row++;
                }

                // Points
                $s2Row += 1;
                foreach ([5, 4] as $score) {
                    $sheet2->setCellValue("A{$s2Row}", $score);
                    $sheet2->setCellValue("B{$s2Row}", $sec['points'][$score]['quality'] ?? 0);
                    $sheet2->setCellValue("C{$s2Row}", $sec['points'][$score]['attitude'] ?? 0);
                    $sheet2->setCellValue("D{$s2Row}", $sec['points'][$score]['safety'] ?? 0);
                    $sheet2->setCellValue("E{$s2Row}", $sec['points'][$score]['time'] ?? 0);
                    $sheet2->setCellValue("F{$s2Row}", $sec['points'][$score]['housekeeping'] ?? 0);
                    $sheet2->getStyle("A{$s2Row}:F{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet2->getStyle("A{$s2Row}")->getFont()->setBold(true);
                    $s2Row++;
                }

                // Means
                $s2Row += 1;
                $sheet2->setCellValue("B{$s2Row}", number_format($sec['means']['quality'] ?? 0, 2));
                $sheet2->setCellValue("C{$s2Row}", number_format($sec['means']['attitude'] ?? 0, 2));
                $sheet2->setCellValue("D{$s2Row}", number_format($sec['means']['safety'] ?? 0, 2));
                $sheet2->setCellValue("E{$s2Row}", number_format($sec['means']['time'] ?? 0, 2));
                $sheet2->setCellValue("F{$s2Row}", number_format($sec['means']['housekeeping'] ?? 0, 2));
                $sheet2->getStyle("B{$s2Row}:F{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle("B{$s2Row}:F{$s2Row}")->getFont()->setBold(true);

                // Overall Box
                $s2Row += 2;
                $sheet2->mergeCells("D{$s2Row}:E{$s2Row}");
                $sheet2->setCellValue("D{$s2Row}", $sec['name']);
                $sheet2->setCellValue("F{$s2Row}", number_format($sec['overall_mean'] ?? 0, 2));
                $sheet2->getStyle("D{$s2Row}:F{$s2Row}")->getFont()->setBold(true);
                $sheet2->getStyle("D{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle("F{$s2Row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle("D{$s2Row}:F{$s2Row}")->applyFromArray($thinBorder);

                $s2Row += 2;
                $sheet2->setBreak("A{$s2Row}", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
                $s2Row++;
            }
        }

        $sheet2->getColumnDimension('A')->setWidth(16);
        $sheet2->getColumnDimension('B')->setWidth(16);
        $sheet2->getColumnDimension('C')->setWidth(16);
        $sheet2->getColumnDimension('D')->setWidth(18);
        $sheet2->getColumnDimension('E')->setWidth(16);
        $sheet2->getColumnDimension('F')->setWidth(18);
        $sheet2->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet2->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet2->getPageSetup()->setFitToWidth(1);
        $sheet2->getPageSetup()->setFitToHeight(0);

        $spreadsheet->setActiveSheetIndex(0);

        $cleanPeriod = str_replace(' ', '_', $periodText);
        $fileName = "Summary_Accomplishment_and_CS_Survey_{$cleanPeriod}.xlsx";

        $tempFile = tempnam(sys_get_temp_dir(), 'linkod_summary_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function getSectionData(Carbon $startDate, Carbon $endDate, ?int $categoryId = null): array
    {
        $definedSections = [
            'PLUMBING SERVICES' => 1,
            'ELECTRICAL SERVICES' => 2,
            'CARPENTRY/MASONRY SERVICES' => 3,
            'LANDSCAPING SERVICES' => 4,
            'JANITORIAL SERVICES' => 5,
            'PAINTING SERVICES' => 6,
            'MANPOWER SERVICES FOR SPECIAL EVENTS' => 7,
        ];

        $query = ServiceRequest::with(['category', 'evaluation', 'latestHistory', 'project.latestHistory'])
            ->where(function($q) {
                $q->whereHas('latestHistory', function($lh) {
                    $lh->where('current_status', 'Completed');
                })->orWhereHas('project.latestHistory', function($plh) {
                    $plh->where('current_status', 'Completed');
                });
            })
            ->whereBetween('submitted_at', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $requests = $query->get();

        $sectionsData = [];
        foreach ($definedSections as $secName => $order) {
            if ($categoryId) {
                $secReqs = $requests->filter(fn($r) => $this->classifySection($r) === $secName);
                $belongsToCategory = match($categoryId) {
                    1 => in_array($secName, ['ELECTRICAL SERVICES', 'CARPENTRY/MASONRY SERVICES']),
                    2 => $secName === 'PLUMBING SERVICES',
                    3 => $secName === 'PAINTING SERVICES',
                    4 => in_array($secName, ['JANITORIAL SERVICES', 'MANPOWER SERVICES FOR SPECIAL EVENTS']),
                    6 => $secName === 'LANDSCAPING SERVICES',
                    default => false,
                };
                if (!$belongsToCategory && $secReqs->isEmpty()) {
                    continue;
                }
            } else {
                $secReqs = $requests->filter(fn($r) => $this->classifySection($r) === $secName);
            }

            $evaluations = $secReqs->pluck('evaluation')->filter();

            $raters = [];
            $rNum = 1;
            $counts = [
                5 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
                4 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
                3 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
                2 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
                1 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
            ];
            $points = [
                5 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
                4 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
                3 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
                2 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
                1 => ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0],
            ];
            $totals = ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0];

            foreach ($evaluations as $ev) {
                $fr = $ev->function_ratings;
                $raters[] = array_merge(['rater_no' => $rNum++], $fr);
                foreach (['quality', 'attitude', 'safety', 'time', 'housekeeping'] as $f) {
                    $score = (int)($fr[$f] ?? 5);
                    if ($score < 1) $score = 1;
                    if ($score > 5) $score = 5;
                    $counts[$score][$f]++;
                    $points[$score][$f] += $score;
                    $totals[$f] += $score;
                }
            }

            $rCount = count($raters);
            $means = ['quality' => 0, 'attitude' => 0, 'safety' => 0, 'time' => 0, 'housekeeping' => 0];
            $overallMean = 0;

            if ($rCount > 0) {
                foreach (['quality', 'attitude', 'safety', 'time', 'housekeeping'] as $f) {
                    $means[$f] = round($totals[$f] / $rCount, 2);
                }
                $overallMean = round(array_sum($means) / 5, 2);
            }

            $sectionsData[$secName] = [
                'name'           => $secName,
                'order'          => $order,
                'total_requests' => $secReqs->count(),
                'cs_result'      => $overallMean,
                'raters'         => $raters,
                'counts'         => $counts,
                'points'         => $points,
                'means'          => $means,
                'overall_mean'   => $overallMean,
            ];
        }

        return $sectionsData;
    }

    public function classifySection($req): string
    {
        $catName = strtolower($req->category->category_name ?? '');
        $title = strtolower($req->title ?? '');

        if (str_contains($catName, 'plumbing')) {
            return 'PLUMBING SERVICES';
        }
        if (str_contains($catName, 'landscaping')) {
            return 'LANDSCAPING SERVICES';
        }
        if (str_contains($catName, 'painting') || str_contains($catName, 'paint')) {
            return 'PAINTING SERVICES';
        }
        if (str_contains($catName, 'janitorial') || str_contains($catName, 'manpower') || str_contains($catName, 'event')) {
            return (!empty($req->is_manpower) || str_contains($title, 'event') || str_contains($catName, 'manpower') || str_contains($catName, 'event'))
                ? 'MANPOWER SERVICES FOR SPECIAL EVENTS' 
                : 'JANITORIAL SERVICES';
        }

        $isElectrical = str_contains($title, 'electric') || str_contains($title, 'light') || str_contains($title, 'aircon') 
            || str_contains($title, 'wire') || str_contains($title, 'breaker') || str_contains($title, 'fan') 
            || str_contains($title, 'outlet') || str_contains($title, 'switch') || str_contains($title, 'power')
            || str_contains($catName, 'electric');

        return $isElectrical ? 'ELECTRICAL SERVICES' : 'CARPENTRY/MASONRY SERVICES';
    }

    public function resolveDateRange(?Request $request = null): array
    {
        $req = $request ?? request();
        $year = $req->filled('report_year') ? (int)$req->report_year : now()->year;
        $period = $req->input('period', 'sem1');

        if ($req->filled('start_date') && $req->filled('end_date')) {
            $startDate = Carbon::parse($req->start_date)->startOfDay();
            $endDate   = Carbon::parse($req->end_date)->endOfDay();
            $year      = $startDate->year;
            $startMonthStr = strtoupper($startDate->format('F'));
            $endMonthStr   = strtoupper($endDate->format('F'));
            $monthRange = ($startMonthStr === $endMonthStr) ? $startMonthStr : "{$startMonthStr} TO {$endMonthStr}";
            $periodText = ($startMonthStr === $endMonthStr) ? "{$startMonthStr} {$year}" : "{$startMonthStr} TO {$endMonthStr} {$year}";
        } elseif ($period === 'sem1') {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 6, 30)->endOfDay();
            $monthRange = 'JANUARY TO JUNE';
            $periodText = "JANUARY TO JUNE {$year}";
        } elseif ($period === 'sem2') {
            $startDate = Carbon::createFromDate($year, 7, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $monthRange = 'JULY TO DECEMBER';
            $periodText = "JULY TO DECEMBER {$year}";
        } elseif ($period === 'year') {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($year, 12, 31)->endOfDay();
            $monthRange = 'JANUARY TO DECEMBER';
            $periodText = "JANUARY TO DECEMBER {$year}";
        } else {
            $startDate = now()->month <= 6 ? Carbon::createFromDate($year, 1, 1)->startOfDay() : Carbon::createFromDate($year, 7, 1)->startOfDay();
            $endDate   = now()->month <= 6 ? Carbon::createFromDate($year, 6, 30)->endOfDay() : Carbon::createFromDate($year, 12, 31)->endOfDay();
            $monthRange = now()->month <= 6 ? 'JANUARY TO JUNE' : 'JULY TO DECEMBER';
            $periodText = now()->month <= 6 ? "JANUARY TO JUNE {$year}" : "JULY TO DECEMBER {$year}";
        }

        return [$startDate, $endDate, $year, $periodText, $monthRange];
    }
}
