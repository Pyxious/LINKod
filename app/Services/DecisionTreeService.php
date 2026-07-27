<?php

namespace App\Services;

use App\Models\ServiceRequest;

class DecisionTreeService
{
    /**
     * Calculate and save the priority for a request.
     */
    public function assignPriority(ServiceRequest $request): string
    {
        $priority = $this->calculate($request);

        $request->update(['priority' => $priority]);

        return $priority;
    }

    protected function calculate(ServiceRequest $request): string
    {
        $title = strtolower($request->title ?? '');
        $description = strtolower($request->description ?? '');
        $combinedText = $title . ' ' . $description;
        
        $categoryName = strtolower($request->category->category_name ?? '');

        // Check if any urgent keywords are present
        if ($this->hasUrgentKeywords($categoryName, $combinedText)) {
            return 'high';
        }

        // If no urgent keywords, priority depends on Location being "BU Main"
        $locationLower = strtolower($request->campus ?? ''); // The form uses "campus" for BU Main, BU Daraga etc.
        if (str_contains($locationLower, 'main')) {
            return 'medium';
        }

        // Default to low if not main campus and not urgent
        return 'low';
    }

    protected function hasUrgentKeywords(string $category, string $text): bool
    {
        $generalKeywords = [
            'urgent', 'emergency', 'immediate', 'asap', 'today', 'now', 'immediately',
            'dangerous', 'unsafe', 'hazard', 'critical', 'severe', 'major',
            'cannot operate', 'unusable', 'blocked', 'collapsed', 'flooded',
            'leaking', 'broken', 'damaged', 'failed', 'outage'
        ];

        $specificKeywords = [];

        if (str_contains($category, 'electrical')) {
            $specificKeywords = [
                'power shortage', 'power outage', 'no current', 'no electricity',
                'exposed wire', 'exposed electrical wiring', 'electrical hazard',
                'short circuit', 'sparking outlet', 'burning smell',
                'breaker keeps tripping', 'electrical fire', 'live wire',
                'damaged electrical panel'
            ];
        } elseif (str_contains($category, 'carpentry') || str_contains($category, 'masonry')) {
            $specificKeywords = [
                'damaged roof', 'collapsing ceiling', 'falling ceiling',
                'leaking roof', 'structural crack', 'cracked wall', 'damaged beam',
                'loose handrail', 'broken stairs', 'unsafe flooring', 'fallen concrete'
            ];
        } elseif (str_contains($category, 'landscaping')) {
            $specificKeywords = [
                'fallen tree', 'fallen tree branch', 'tree blocking',
                'hanging branch', 'obstructed walkway', 'blocked roadway',
                'blocked drainage', 'storm debris'
            ];
        } elseif (str_contains($category, 'janitorial')) {
            $specificKeywords = [
                'flooded restroom', 'slippery floor', 'foul odor',
                'overflowing garbage', 'chemical spill', 'hazardous spill',
                'biohazard', 'blood spill', 'sewage spill'
            ];
        } elseif (str_contains($category, 'plumbing')) {
            $specificKeywords = [
                'clogged p-trap', 'no water coming out', 'no water supply',
                'broken waterline', 'declogged water source', 'leaking pipe',
                'electrical pump not functioning', 'installation of jetmatic',
                'emergency installation', 'water leak', 'damaged water pipe',
                'repair toilet flush', 'water leaking', 'broken water pump',
                'sink drain unclogging', 'burst pipe', 'major leak', 'flooding',
                'water overflow', 'septic overflow', 'clogged main drain',
                'leaking faucet', 'overflowing toilet', 'water gushing'
            ];
        } elseif (str_contains($category, 'painting')) {
            $specificKeywords = [
                'faded pedestrian lane', 'faded safety markings',
                'emergency exit marking', 'hazard marking', 'safety line repainting'
            ];
        } elseif (str_contains($category, 'manpower')) {
            $specificKeywords = [
                'typhoon', 'graduation', 'commencement', 'university event',
                'emergency setup', 'event tomorrow', 'vip visit',
                'evacuation setup', 'urgent hauling'
            ];
        }

        $allKeywords = array_merge($generalKeywords, $specificKeywords);

        foreach ($allKeywords as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
