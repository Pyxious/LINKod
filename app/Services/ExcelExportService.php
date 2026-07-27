<?php

namespace App\Services;

use App\Models\ServiceRequest;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class ExcelExportService
{
    /**
     * Fills the Excel template with request data and returns the path to the generated file.
     */
    public function generateJobRequest(ServiceRequest $request): string
    {
        $templatePath = storage_path('app/forms/Job-Request.xlsx');
        
        // Load the template
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Service Category Title (e.g. PLUMBING)
        $categoryName = strtoupper($request->category->category_name ?? '');
        $sheet->setCellValue('A7', $categoryName);

        // Basic Info
        $clientName = $request->client->user->first_name . ' ' . $request->client->user->last_name;
        $sheet->setCellValue('C10', $clientName); // Guessing C10 based on structure
        $sheet->setCellValue('J10', $request->submitted_at->format('F d, Y')); // Date
        
        $sheet->setCellValue('C11', $request->location); // Office/Location
        $sheet->setCellValue('J11', $request->client->contact_number ?? ''); // Contact No
        
        // Description
        $sheet->setCellValue('B14', $request->title . " - " . $request->description);

        // Priority Checkboxes
        $priority = strtolower($request->priority);
        // Assuming checkbox box is in B25 for High, and H25 for Routine based on labels at C25 and I25
        if ($priority === 'high') {
            $sheet->setCellValue('B25', 'X'); // Mark High
        } elseif ($priority === 'medium' || $priority === 'low') {
            $sheet->setCellValue('H25', 'X'); // Mark Routine
        }

        // Assigned Personnel (if approved/assigned)
        // Usually handled by a related project worker, but let's leave placeholder
        if ($request->project && $request->project->workers->count() > 0) {
            $workerNames = $request->project->workers->map(fn($w) => $w->user->first_name . ' ' . $w->user->last_name)->implode(', ');
            $sheet->setCellValue('G29', $workerNames); // Assuming G29 based on label at G28
        }

        // Output file naming
        $safeClientName = Str::slug($clientName);
        $fileName = "Service-Request-{$request->request_id}-{$safeClientName}.html";
        $tempPath = storage_path("app/temp/{$fileName}");

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
        $writer->save($tempPath);
        
        // Read HTML to inject CSS and Javascript
        $html = file_get_contents($tempPath);
        
        // Remove hardcoded width/height from images so our CSS can control them
        $html = preg_replace('/<img([^>]*)width="[0-9]+"/', '<img$1', $html);
        $html = preg_replace('/<img([^>]*)height="[0-9]+"/', '<img$1', $html);
        
        $cssAndJs = "
        <style>
            table {
                width: 100% !important;
                max-width: 100% !important;
            }
            img { 
                object-fit: contain; 
                max-width: 150px !important; 
                max-height: 80px !important; 
                height: auto !important; 
                width: auto !important; 
            } 
            @media print { 
                @page { 
                    size: 8.5in 13in portrait; 
                    margin: 0.5in; 
                } 
                body { 
                    zoom: 0.65; 
                } 
            }
        </style>
        <script>
            window.onload = function() { window.print(); }
        </script>
        ";
        
        $html = str_replace('</head>', $cssAndJs . '</head>', $html);
        file_put_contents($tempPath, $html);

        return $tempPath;
    }
}
