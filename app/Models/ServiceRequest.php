<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    // Named ServiceRequest to avoid collision with Laravel's Request class
    protected $table = 'request';
    protected $primaryKey = 'request_id';
    public $timestamps = false;

    protected $fillable = [
        'client_id', 'category_id', 'title', 'description',
        'campus', 'location', 'complexity', 'urgency', 'priority',
        'attachment', 'submitted_at',
        'scheduled_date', 'scheduled_time_window', 'schedule_status', 'schedule_decline_reason', 'bom_status',
    ];

    protected $casts = [
        'submitted_at'   => 'datetime',
        'scheduled_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $request) {
            static::bustRecurringCache($request->submitted_at?->format('Y-m'));
            \App\Events\RequestUpdated::broadcastUpdate((int) $request->request_id, null, 'request_saved');
        });

        static::deleted(function (self $request) {
            static::bustRecurringCache($request->submitted_at?->format('Y-m'));
            \App\Events\RequestUpdated::broadcastUpdate((int) $request->request_id, null, 'request_deleted');
        });
    }

    public static function bustRecurringCache(?string $yearMonth = null): void
    {
        static::$monthRowsCache = [];
        static::$recurringCountsCache = [];

        $ym = $yearMonth ?: now()->format('Y-m');
        \Illuminate\Support\Facades\Cache::forget("req_rec_month_rows_v3_{$ym}");
        \Illuminate\Support\Facades\Cache::forget("req_rec_month_rows_v2_{$ym}");
        \Illuminate\Support\Facades\Cache::forget("req_rec_month_rows_{$ym}");
        \Illuminate\Support\Facades\Cache::forget('admin_requests_table_kpis');
        \Illuminate\Support\Facades\Cache::forget('admin_requests_table_kpis_v2');
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_kpis');
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
    }

    public function getIsUrgentAttribute(): bool
    {
        return in_array(strtolower($this->priority ?? ''), ['urgent', 'high']);
    }

    public function getIsRoutineAttribute(): bool
    {
        return !$this->is_urgent;
    }

    public function getPriorityLabelAttribute(): string
    {
        return $this->is_urgent ? 'High' : 'Routine';
    }

    public function getFormattedIdAttribute(): string
    {
        return 'REQ-' . str_pad((string)$this->request_id, 4, '0', STR_PAD_LEFT);
    }

    public function getTrackingCodeAttribute(): string
    {
        $catName = strtolower($this->category?->category_name ?? '');
        $prefix = match (true) {
            $this->is_manpower => 'MAN',
            str_contains($catName, 'janitorial') => 'JS',
            str_contains($catName, 'landscaping') => 'LS',
            str_contains($catName, 'plumbing') => 'PLS',
            str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAS',
            str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
            str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
            default => 'REQ',
        };

        return $prefix . '-' . str_pad((string) $this->request_id, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Determine whether this request matches a search query across tracking codes
     * (e.g. "cms", "CMS-010", "CMS 10", "#0010"), title, campus, location, description,
     * category, and status, supporting multi-word keyword queries.
     */
    public function matchesSearchQuery(string $rawSearch): bool
    {
        $q = strtolower(trim($rawSearch));
        if ($q === '') {
            return true;
        }

        $trackingCode = strtolower($this->tracking_code);
        $trackingPrefix = explode('-', $trackingCode)[0] ?? 'req';
        $trackingCompact = str_replace('-', '', $trackingCode);
        $padded3 = str_pad((string) $this->request_id, 3, '0', STR_PAD_LEFT);
        $padded4 = str_pad((string) $this->request_id, 4, '0', STR_PAD_LEFT);

        // Support space-separated prefix + number such as "cms 10" or "cms #010"
        if (preg_match('/^([a-z]{1,6})\s+#?0*([1-9]\d*)$/', $q, $spaceMatch)) {
            $typedPfx = match ($spaceMatch[1]) {
                'paint' => 'pas',
                'ps'    => 'pls',
                default => $spaceMatch[1],
            };
            if ((int) $spaceMatch[2] === (int) $this->request_id && ($typedPfx === 'req' || $typedPfx === $trackingPrefix)) {
                return true;
            }
        }

        $rawStatus = strtolower($this->current_status ?? 'submitted');
        $displayStatus = match ($rawStatus) {
            'rejected' => 'disapproved',
            'awaiting verification of bill of materials', 'awaiting verification of list of materials' => 'approved',
            'bom verified (awaiting client approval)', 'list of materials verified (awaiting client approval)' => 'materials verified approve materials',
            'awaiting materials', 'waiting for materials' => 'awaiting materials',
            'materials arrived' => 'materials arrived',
            'pending verification' => 'final check',
            default => $rawStatus,
        };

        $textHaystack = strtolower(implode(' ', [
            $this->title ?? '',
            $this->location ?? '',
            $this->campus ?? '',
            static::normalizeDescriptionForComparison($this->description),
            $this->category?->display_name ?? '',
            $this->category?->category_name ?? '',
            $rawStatus,
            $displayStatus,
        ]));

        $codeCandidates = [
            $trackingCode,
            $trackingCompact,
            'req-' . $padded3,
            'req-' . $padded4,
            'req' . $padded3,
            'req' . $padded4,
        ];

        $tokens = array_values(array_filter(preg_split('/\s+/', $q) ?: []));
        foreach ($tokens as $tok) {
            // 1. Prefix-anchored match on tracking codes (so "ls" matches "LS-053", NOT "PLS-034")
            $matchedCode = false;
            foreach ($codeCandidates as $cand) {
                if (str_starts_with($cand, $tok)) {
                    $matchedCode = true;
                    break;
                }
            }
            if ($matchedCode) {
                continue;
            }

            // 2. Digit / #ID match (e.g. "#51", "051", "0051", "51")
            $cleanDigits = ltrim($tok, '#');
            if ($cleanDigits !== '' && ctype_digit($cleanDigits)) {
                if ((int) $cleanDigits === (int) $this->request_id || str_contains($padded4, $cleanDigits)) {
                    continue;
                }
            }

            // 3. Flexible prefixed ID match (e.g. "cms-51", "cms-0051", "req-51")
            if (preg_match('/^([a-z]{1,6})[-#]+0*([1-9]\d*)$/', $tok, $codeMatch) && (int) $codeMatch[2] === (int) $this->request_id) {
                $typedPfx = match ($codeMatch[1]) {
                    'paint' => 'pas',
                    'ps'    => 'pls',
                    default => $codeMatch[1],
                };
                if ($typedPfx === 'req' || $typedPfx === $trackingPrefix) {
                    continue;
                }
            }

            // 4. Keyword match anywhere in title, campus, location, description, category, or status
            if (str_contains($textHaystack, $tok)) {
                continue;
            }

            return false;
        }

        return true;
    }

    public function getFormattedScheduleAttribute(): string
    {
        if (!$this->scheduled_date) return '';
        $windowText = match(strtoupper($this->scheduled_time_window ?? '')) {
            'AM' => 'Morning (8:00 AM – 12:00 PM)',
            'PM' => 'Afternoon (1:00 PM – 5:00 PM)',
            'AM-PM' => 'Whole Day (8:00 AM – 5:00 PM)',
            default => $this->scheduled_time_window ?: 'Visit'
        };
        return $this->scheduled_date->format('M j, Y') . ' • ' . $windowText;
    }

    public function isAccepted(): bool
    {
        if (in_array($this->current_status, ['Submitted', 'Pending', 'Schedule Set', 'Schedule Refused', 'Cancelled', 'Rejected'])) {
            return false;
        }

        if (!$this->project) {
            return false;
        }

        return !empty($this->project->date_approved) || in_array($this->current_status, [
            'Approved',
            'Schedule Confirmed',
            'Awaiting Verification of Bill of Materials',
            'BOM Verified (Awaiting Client Approval)',
            'Awaiting Materials',
            'Materials Arrived',
            'In Progress',
            'On Hold',
            'Pending Verification',
            'Completed',
        ]);
    }

    public function isScheduleApproved(): bool
    {
        return (!empty($this->scheduled_date) && $this->schedule_status === 'approved') || $this->isAccepted();
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id')->withTrashed();
    }

    public function histories()
    {
        return $this->hasMany(RequestHistory::class, 'request_id', 'request_id')
                    ->orderBy('updated_at', 'asc')
                    ->orderBy('history_id', 'asc');
    }

    public function evaluation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Evaluation::class, 'request_id', 'request_id');
    }

    public function project()
    {
        return $this->hasOne(Project::class, 'request_id', 'request_id');
    }

    public function latestHistory()
    {
        return $this->hasOne(RequestHistory::class, 'request_id', 'request_id')
                    ->latestOfMany('history_id');
    }

    public function messages()
    {
        return $this->hasMany(RequestMessage::class, 'request_id', 'request_id')->oldest();
    }

    public function isResolved(): bool
    {
        $status = strtolower($this->current_status);
        $projStatus = strtolower($this->project?->current_status ?? '');
        return in_array($status, ['completed', 'cancelled', 'rejected']) || $projStatus === 'completed';
    }

    public function getCurrentStatusAttribute(): string
    {
        return $this->latestHistory?->current_status ?? 'Submitted';
    }

    /**
     * Normalize plain text for case-insensitive, whitespace-insensitive comparison.
     */
    public static function normalizeText(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return strtolower((string) preg_replace('/\s+/', ' ', trim($value)));
    }

    /**
     * Normalize description text (supporting both plain text and Category 4 JSON payloads)
     * so recurring/duplicate checks compare actual request details accurately.
     */
    public static function normalizeDescriptionForComparison(?string $description): string
    {
        $raw = trim((string) ($description ?? ''));
        if ($raw === '') {
            return '';
        }

        if (str_starts_with($raw, '{') && str_ends_with($raw, '}')) {
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                if (($parsed['type'] ?? '') === 'janitorial' || isset($parsed['janitorial_areas'])) {
                    return implode('|', [
                        'janitorial',
                        static::normalizeText($parsed['janitorial_areas'] ?? ''),
                        static::normalizeText($parsed['janitorial_type'] ?? ''),
                        static::normalizeText($parsed['janitorial_frequency'] ?? ''),
                        static::normalizeText($parsed['janitorial_supplies'] ?? ''),
                        static::normalizeText($parsed['general_description'] ?? ''),
                    ]);
                }

                return implode('|', [
                    'manpower',
                    static::normalizeText($parsed['activity_title'] ?? ''),
                    static::normalizeText($parsed['venue'] ?? ''),
                    static::normalizeText($parsed['prep_details'] ?? ''),
                    static::normalizeText($parsed['assistance_details'] ?? ''),
                    static::normalizeText($parsed['clearing_details'] ?? ''),
                    static::normalizeText($parsed['additional_notes'] ?? ''),
                    static::normalizeText($parsed['general_description'] ?? ''),
                ]);
            }
        }

        return static::normalizeText($raw);
    }

    /**
     * Build a canonical comparison signature for "same details" matching based on structured fields:
     * category_id + campus + location + specific concern (title).
     * Free-text description is intentionally excluded so minor wording changes ("yes" vs "yes no")
     * cannot bypass duplicate blocking, monthly caps, or recurring issue detection.
     */
    public static function buildSameDetailsSignature(
        int $categoryId,
        ?string $title,
        ?string $campus,
        ?string $location,
        ?string $description = null
    ): string {
        return implode('||', [
            (string) $categoryId,
            static::normalizeText($campus),
            static::normalizeText($location),
            static::normalizeText($title),
        ]);
    }

    /**
     * Determine whether this request still counts toward a client's active request quota (max 5).
     * A slot is only freed when the request is Disapproved (Rejected), Cancelled/Merged,
     * or Completed AND Rated (has an evaluation).
     */
    public function countsTowardClientQuota(): bool
    {
        $status = strtolower($this->current_status);
        $projStatus = strtolower($this->project?->current_status ?? '');

        if (in_array($status, ['rejected', 'disapproved', 'cancelled'], true)) {
            return false;
        }

        $isCompleted = ($status === 'completed' || $projStatus === 'completed');
        $hasEvaluation = $this->relationLoaded('evaluation')
            ? ($this->evaluation !== null)
            : $this->evaluation()->exists();

        if ($isCompleted && $hasEvaluation) {
            return false;
        }

        return true;
    }

    /**
     * Fetch all unfinished requests across all services for a client that still count toward the 5-request cap.
     */
    public static function getUnfinishedRequestsForClient(int $clientId): \Illuminate\Support\Collection
    {
        return static::with(['latestHistory', 'project.latestHistory', 'evaluation', 'category'])
            ->where('client_id', $clientId)
            ->whereDoesntHave('latestHistory', function ($q) {
                $q->whereIn('current_status', ['Rejected', 'Disapproved', 'Cancelled']);
            })
            ->whereNot(function ($q) {
                $q->whereHas('evaluation')
                  ->where(function ($cq) {
                      $cq->whereHas('latestHistory', fn ($lh) => $lh->where('current_status', 'Completed'))
                         ->orWhereHas('project.latestHistory', fn ($ph) => $ph->where('current_status', 'Completed'));
                  });
            })
            ->orderBy('request_id', 'asc')
            ->get()
            ->filter(fn (self $req) => $req->countsTowardClientQuota())
            ->values();
    }

    /**
     * Count all unfinished requests across all services for a client (max 5 allowed).
     */
    public static function countUnfinishedRequestsForClient(int $clientId): int
    {
        return static::getUnfinishedRequestsForClient($clientId)->count();
    }

    /**
     * Count unfinished requests submitted by the same client with the exact same structured details
     * (category_id + campus + location + title). Clients are allowed up to 4 max.
     */
    public static function countClientSameDetailsRequests(
        int $clientId,
        int $categoryId,
        string $title,
        string $campus,
        string $location
    ): int {
        $targetSig = static::buildSameDetailsSignature($categoryId, $title, $campus, $location);

        return static::getUnfinishedRequestsForClient($clientId)
            ->filter(fn (self $req) => static::buildSameDetailsSignature(
                (int) $req->category_id,
                $req->title,
                $req->campus,
                $req->location
            ) === $targetSig)
            ->count();
    }

    /**
     * Find an existing unfinished request by the same client that has BOTH the same structured details
     * AND the exact same normalized description (used to warn the client before submitting).
     */
    public static function findActiveSameDescriptionForClient(
        int $clientId,
        int $categoryId,
        string $title,
        string $campus,
        string $location,
        ?string $description
    ): ?self {
        $targetSig = static::buildSameDetailsSignature($categoryId, $title, $campus, $location);
        $targetDesc = static::normalizeDescriptionForComparison($description);

        foreach (static::getUnfinishedRequestsForClient($clientId) as $candidate) {
            $sig = static::buildSameDetailsSignature(
                (int) $candidate->category_id,
                $candidate->title,
                $candidate->campus,
                $candidate->location
            );

            if ($sig === $targetSig && static::normalizeDescriptionForComparison($candidate->description) === $targetDesc) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Find an existing active (unresolved) request submitted by the same client with the exact same details.
     */
    public static function findActiveDuplicateForClient(
        int $clientId,
        int $categoryId,
        string $title,
        string $campus,
        string $location,
        ?string $description
    ): ?self {
        $targetSig = static::buildSameDetailsSignature($categoryId, $title, $campus, $location, $description);

        $candidates = static::with(['latestHistory', 'project', 'category'])
            ->where('client_id', $clientId)
            ->where('category_id', $categoryId)
            ->whereDoesntHave('latestHistory', function ($q) {
                $q->whereIn('current_status', ['Completed', 'Cancelled', 'Rejected']);
            })
            ->orderBy('request_id', 'asc')
            ->get();

        foreach ($candidates as $candidate) {
            if ($candidate->isResolved()) {
                continue;
            }

            $sig = static::buildSameDetailsSignature(
                (int) $candidate->category_id,
                $candidate->title,
                $candidate->campus,
                $candidate->location,
                $candidate->description
            );

            if ($sig === $targetSig) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Count valid (non-Cancelled, non-Rejected) requests with the exact same details in the current calendar month.
     */
    public static function countMonthlySameDetailsRequests(
        int $categoryId,
        string $title,
        string $campus,
        string $location,
        ?string $description,
        ?\Carbon\CarbonInterface $referenceDate = null
    ): int {
        $date = $referenceDate ?: now();
        $targetSig = static::buildSameDetailsSignature($categoryId, $title, $campus, $location, $description);

        $candidates = static::with('latestHistory')
            ->where('category_id', $categoryId)
            ->whereYear('submitted_at', $date->year)
            ->whereMonth('submitted_at', $date->month)
            ->whereDoesntHave('latestHistory', function ($q) {
                $q->whereIn('current_status', ['Cancelled', 'Rejected']);
            })
            ->get(['request_id', 'category_id', 'title', 'campus', 'location', 'description']);

        $count = 0;
        foreach ($candidates as $candidate) {
            $sig = static::buildSameDetailsSignature(
                (int) $candidate->category_id,
                $candidate->title,
                $candidate->campus,
                $candidate->location,
                $candidate->description
            );
            if ($sig === $targetSig) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Find all other unresolved requests (both older AND newer) sharing the same category, campus, and location
     * so GSO Admin can select and merge duplicates from either the first entry or any duplicate entry.
     */
    public function findPotentialPrimaryDuplicates(): \Illuminate\Support\Collection
    {
        if ($this->isResolved()) {
            return collect();
        }

        $normCampus = static::normalizeText($this->campus);
        $normLocation = static::normalizeText($this->location);
        $normTitle = static::normalizeText($this->title);

        return static::with(['client.user', 'latestHistory', 'project', 'category'])
            ->where('request_id', '!=', $this->request_id)
            ->where('category_id', $this->category_id)
            ->whereDoesntHave('latestHistory', function ($q) {
                $q->whereIn('current_status', ['Completed', 'Cancelled', 'Rejected']);
            })
            ->orderBy('request_id', 'asc')
            ->get()
            ->filter(function (self $other) use ($normCampus, $normLocation) {
                if ($other->isResolved()) {
                    return false;
                }

                return static::normalizeText($other->campus) === $normCampus
                    && static::normalizeText($other->location) === $normLocation;
            })
            ->sortBy(function (self $other) use ($normTitle) {
                $sameTitle = static::normalizeText($other->title) === $normTitle ? 0 : 1;

                return sprintf('%d-%010d', $sameTitle, $other->request_id);
            })
            ->values();
    }

    /**
     * Merge one or more duplicate ServiceRequest IDs into a single Primary (First) ServiceRequest.
     * Returns the count of merged duplicate requests.
     */
    public static function mergeDuplicatesIntoPrimary(
        self $primary,
        iterable $duplicateIds,
        int $adminUserId,
        ?string $remarks = null,
        ?\App\Services\NotificationService $notifications = null
    ): int {
        $ids = collect($duplicateIds)
            ->map(fn ($v) => (int) $v)
            ->filter(fn (int $v) => $v > 0 && $v !== (int) $primary->request_id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        if (in_array($primary->current_status, ['Cancelled', 'Rejected'], true)) {
            throw new \RuntimeException('Cannot merge into a cancelled or disapproved requisition.');
        }

        $duplicates = static::with(['client.user', 'project.workers', 'category', 'latestHistory'])
            ->whereIn('request_id', $ids->all())
            ->orderBy('request_id', 'asc')
            ->get();

        $primaryPad = str_pad((string) $primary->request_id, 4, '0', STR_PAD_LEFT);
        $extraRemarks = trim((string) ($remarks ?? ''));
        $mergedSummaries = [];
        $mergedCount = 0;

        foreach ($duplicates as $duplicate) {
            if ($duplicate->isResolved()) {
                continue;
            }

            $duplicatePrevious = $duplicate->current_status;
            $duplicatePad = str_pad((string) $duplicate->request_id, 4, '0', STR_PAD_LEFT);

            // 1. Release assigned workers / cancel project on duplicate if any
            if ($duplicate->project) {
                foreach ($duplicate->project->workers as $worker) {
                    $hasOtherActive = $worker->projects()
                        ->where('project.project_id', '!=', $duplicate->project->project_id)
                        ->whereHas('latestHistory', fn ($lh) => $lh->whereNotIn('current_status', ['Completed', 'Cancelled']))
                        ->exists();
                    if (!$hasOtherActive) {
                        $worker->update(['is_available' => true]);
                    }
                }
                $duplicate->project->workers()->detach();
                $duplicate->project->update(['current_status' => 'Cancelled']);

                ProjectHistory::create([
                    'project_id'      => $duplicate->project->project_id,
                    'previous_status' => $duplicate->project->current_status,
                    'current_status'  => 'Cancelled',
                    'updated_at'      => now(),
                    'updated_by'      => $adminUserId,
                ]);
            }

            // 2. Preserve attachment on primary if primary lacks one, and consolidate messages
            if (!empty($duplicate->attachment) && empty($primary->attachment)) {
                $primary->update(['attachment' => $duplicate->attachment]);
                $primary->attachment = $duplicate->attachment;
            }

            RequestMessage::where('request_id', $duplicate->request_id)
                ->update(['request_id' => $primary->request_id]);

            // 3. Clear schedule fields and close duplicate as Merged (Cancelled)
            $duplicate->update([
                'scheduled_date'          => null,
                'scheduled_time_window'   => null,
                'schedule_status'         => null,
                'schedule_decline_reason' => null,
            ]);

            RequestHistory::create([
                'request_id'      => $duplicate->request_id,
                'previous_status' => $duplicatePrevious,
                'current_status'  => 'Cancelled',
                'remarks'         => "Merged as a duplicate into primary requisition {$primary->tracking_code} (#{$primaryPad}) by GSO Admin."
                    . ($extraRemarks !== '' ? " Note: {$extraRemarks}" : ''),
                'updated_at'      => now(),
                'updated_by'      => $adminUserId,
            ]);

            $submitterName = trim(($duplicate->client?->user?->first_name ?? '') . ' ' . ($duplicate->client?->user?->last_name ?? '')) ?: 'Client';
            $plainDesc = trim((string) ($duplicate->description ?? ''));
            if (str_starts_with($plainDesc, '{') && str_ends_with($plainDesc, '}')) {
                $parsed = json_decode($plainDesc, true);
                $plainDesc = trim((string) ($parsed['general_description'] ?? $parsed['activity_title'] ?? ''));
            }
            $descSnippet = $plainDesc !== '' ? ' ("' . \Illuminate\Support\Str::limit($plainDesc, 60) . '")' : '';
            $mergedSummaries[] = "{$duplicate->tracking_code} (#{$duplicatePad} by {$submitterName}{$descSnippet})";

            // Consolidate distinct duplicate description onto primary requisition
            if (
                $plainDesc !== ''
                && static::normalizeDescriptionForComparison($duplicate->description) !== static::normalizeDescriptionForComparison($primary->description)
            ) {
                $mergeNoteLine = "[Merged from {$duplicate->tracking_code}]: {$plainDesc}";
                $rawPrimaryDesc = trim((string) ($primary->description ?? ''));

                if (str_starts_with($rawPrimaryDesc, '{') && str_ends_with($rawPrimaryDesc, '}')) {
                    $primaryParsed = json_decode($rawPrimaryDesc, true);
                    if (is_array($primaryParsed)) {
                        $existingGen = trim((string) ($primaryParsed['general_description'] ?? ''));
                        if (!str_contains(strtolower($existingGen), strtolower($plainDesc))) {
                            $primaryParsed['general_description'] = $existingGen !== ''
                                ? "{$existingGen}\n{$mergeNoteLine}"
                                : $mergeNoteLine;
                            $newPrimaryDesc = json_encode($primaryParsed);
                            $primary->update(['description' => $newPrimaryDesc]);
                            $primary->description = $newPrimaryDesc;
                        }
                    }
                } elseif (!str_contains(strtolower($rawPrimaryDesc), strtolower($plainDesc))) {
                    $newPrimaryDesc = $rawPrimaryDesc !== ''
                        ? "{$rawPrimaryDesc}\n\n{$mergeNoteLine}"
                        : $mergeNoteLine;
                    $primary->update(['description' => $newPrimaryDesc]);
                    $primary->description = $newPrimaryDesc;
                }
            }

            if ($notifications && $duplicate->client?->user_id) {
                $sameClient = (int) $duplicate->client_id === (int) $primary->client_id;
                $targetUrl = $sameClient
                    ? route('client.requests.show', $primary->request_id, false)
                    : route('client.requests.show', $duplicate->request_id, false);

                $notifications->send(
                    $duplicate->client->user_id,
                    'request_status_changed',
                    'Duplicate Request Consolidated',
                    "Your requisition {$duplicate->tracking_code} ({$duplicate->title}) has been merged into primary requisition {$primary->tracking_code} (#{$primaryPad}) covering the same location and concern.",
                    $targetUrl
                );
            }

            static::bustRecurringCache($duplicate->submitted_at?->format('Y-m'));
            $mergedCount++;
        }

        if ($mergedCount > 0) {
            $primaryStatus = $primary->current_status;
            RequestHistory::create([
                'request_id'      => $primary->request_id,
                'previous_status' => $primaryStatus,
                'current_status'  => $primaryStatus,
                'remarks'         => 'Merged duplicate ' . \Illuminate\Support\Str::plural('requisition', $mergedCount) . ': '
                    . implode(', ', $mergedSummaries) . '.'
                    . ($extraRemarks !== '' ? " Admin note: {$extraRemarks}" : ''),
                'updated_at'      => now(),
                'updated_by'      => $adminUserId,
            ]);

            UserLog::create([
                'user_id'    => $adminUserId,
                'action'     => "Admin merged {$mergedCount} duplicate request(s) into primary request #{$primary->request_id}",
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);

            static::bustRecurringCache($primary->submitted_at?->format('Y-m'));
        }

        return $mergedCount;
    }

    /**
     * Check if this request is recurring (4+ valid requests with the exact same details within the same month)
     */
    public function getIsRecurringAttribute(): bool
    {
        return $this->recurring_count >= 4;
    }

    public static array $recurringCountsCache = [];
    public static array $monthRowsCache = [];
    public ?int $cachedRecurringCount = null;

    /**
     * Warm recurring counts for a collection of requests to avoid N+1 queries.
     */
    public static function warmRecurringCounts($requests): void
    {
        if (empty($requests)) {
            return;
        }

        foreach ($requests as $req) {
            if ($req instanceof self) {
                $req->cachedRecurringCount = $req->recurring_count;
            }
        }
    }

    /**
     * Get total count of valid (non-Cancelled, non-Rejected) requests with the exact same details
     * (category, campus, location, title, and description) in the same calendar month.
     */
    public function getRecurringCountAttribute(): int
    {
        if ($this->cachedRecurringCount !== null) {
            return $this->cachedRecurringCount;
        }

        if (!$this->submitted_at || in_array($this->current_status, ['Cancelled', 'Rejected'], true)) {
            return $this->cachedRecurringCount = 1;
        }

        $date = $this->submitted_at;
        $ym = $date->format('Y-m');
        $signature = static::buildSameDetailsSignature(
            (int) $this->category_id,
            $this->title,
            $this->campus,
            $this->location,
            $this->description
        );
        $cacheKey = $ym . ':' . md5($signature);

        if (isset(static::$recurringCountsCache[$cacheKey])) {
            return $this->cachedRecurringCount = static::$recurringCountsCache[$cacheKey];
        }

        if (!isset(static::$monthRowsCache[$ym])) {
            static::$monthRowsCache[$ym] = \Illuminate\Support\Facades\Cache::remember("req_rec_month_rows_v3_{$ym}", 300, function () use ($date) {
                return static::whereYear('submitted_at', $date->year)
                    ->whereMonth('submitted_at', $date->month)
                    ->whereDoesntHave('latestHistory', function ($q) {
                        $q->whereIn('current_status', ['Cancelled', 'Rejected']);
                    })
                    ->get(['category_id', 'title', 'campus', 'location'])
                    ->map(fn ($r) => static::buildSameDetailsSignature(
                        (int) $r->category_id,
                        $r->title,
                        $r->campus,
                        $r->location
                    ))
                    ->countBy()
                    ->all();
            });
        }

        $count = max(1, (int) (static::$monthRowsCache[$ym][$signature] ?? 1));
        static::$recurringCountsCache[$cacheKey] = $count;

        return $this->cachedRecurringCount = $count;
    }

    /**
     * Scope to filter recurring requests (4+ valid requests with the exact same structured details in the same calendar month)
     */
    public function scopeRecurring($query)
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        $excludeInvalidStatusesSql = "
            NOT EXISTS (
                SELECT 1 FROM request_history rh1
                WHERE rh1.request_id = r1.request_id
                  AND rh1.history_id = (SELECT MAX(rh1m.history_id) FROM request_history rh1m WHERE rh1m.request_id = r1.request_id)
                  AND rh1.current_status IN ('Cancelled', 'Rejected')
            )
            AND NOT EXISTS (
                SELECT 1 FROM request_history rh2
                WHERE rh2.request_id = r2.request_id
                  AND rh2.history_id = (SELECT MAX(rh2m.history_id) FROM request_history rh2m WHERE rh2m.request_id = r2.request_id)
                  AND rh2.current_status IN ('Cancelled', 'Rejected')
            )
        ";

        $sameDetailsSql = "
            r1.category_id = r2.category_id
            AND LOWER(TRIM(COALESCE(r1.campus, ''))) = LOWER(TRIM(COALESCE(r2.campus, '')))
            AND LOWER(TRIM(COALESCE(r1.location, ''))) = LOWER(TRIM(COALESCE(r2.location, '')))
            AND LOWER(TRIM(COALESCE(r1.title, ''))) = LOWER(TRIM(COALESCE(r2.title, '')))
        ";

        if ($driver === 'pgsql') {
            return $query->whereIn('request_id', function ($sub) use ($sameDetailsSql, $excludeInvalidStatusesSql) {
                $sub->select('r1.request_id')
                    ->from('request as r1')
                    ->join('request as r2', function ($join) use ($sameDetailsSql) {
                        $join->whereRaw('EXTRACT(MONTH FROM r1.submitted_at) = EXTRACT(MONTH FROM r2.submitted_at)')
                             ->whereRaw('EXTRACT(YEAR FROM r1.submitted_at) = EXTRACT(YEAR FROM r2.submitted_at)')
                             ->whereRaw($sameDetailsSql);
                    })
                    ->whereRaw($excludeInvalidStatusesSql)
                    ->groupBy('r1.request_id')
                    ->havingRaw('COUNT(r2.request_id) >= 4');
            });
        }

        if ($driver === 'sqlite') {
            return $query->whereIn('request_id', function ($sub) use ($sameDetailsSql, $excludeInvalidStatusesSql) {
                $sub->select('r1.request_id')
                    ->from('request as r1')
                    ->join('request as r2', function ($join) use ($sameDetailsSql) {
                        $join->whereRaw("strftime('%Y-%m', r1.submitted_at) = strftime('%Y-%m', r2.submitted_at)")
                             ->whereRaw($sameDetailsSql);
                    })
                    ->whereRaw($excludeInvalidStatusesSql)
                    ->groupBy('r1.request_id')
                    ->havingRaw('COUNT(r2.request_id) >= 4');
            });
        }

        // MySQL / MariaDB
        return $query->whereIn('request_id', function ($sub) use ($sameDetailsSql, $excludeInvalidStatusesSql) {
            $sub->select('r1.request_id')
                ->from('request as r1')
                ->join('request as r2', function ($join) use ($sameDetailsSql) {
                    $join->whereRaw('MONTH(r1.submitted_at) = MONTH(r2.submitted_at)')
                         ->whereRaw('YEAR(r1.submitted_at) = YEAR(r2.submitted_at)')
                         ->whereRaw($sameDetailsSql);
                })
                ->whereRaw($excludeInvalidStatusesSql)
                ->groupBy('r1.request_id')
                ->havingRaw('COUNT(r2.request_id) >= 4');
        });
    }

    public function getIsManpowerAttribute(): bool
    {
        $desc = $this->description ?? '';
        if (str_starts_with(trim($desc), '{') && str_ends_with(trim($desc), '}')) {
            $data = json_decode($desc, true);
            if (is_array($data)) {
                if (($data['type'] ?? '') === 'janitorial' || isset($data['janitorial_areas'])) {
                    return false;
                }
                if (isset($data['prep_details']) || isset($data['assistance_details']) || isset($data['clearing_details']) || isset($data['activity_title']) || isset($data['event_date']) || isset($data['venue']) || ($data['type'] ?? '') === 'manpower') {
                    return true;
                }
            }
        }

        $catName = strtolower($this->category->category_name ?? '');
        if ((int)$this->category_id !== 4 && !str_contains($catName, 'manpower')) {
            return false;
        }

        $titleLower = strtolower($this->title ?? '');
        $janitorialKeywords = ['clean', 'waste', 'garbage', 'sanitation', 'restroom', 'janitor', 'disinfect', 'housekeeping', 'grass', 'lawn', 'mowing'];
        foreach ($janitorialKeywords as $kw) {
            if (str_contains($titleLower, $kw)) {
                return false;
            }
        }

        return true;
    }

    public function getIsJanitorialAttribute(): bool
    {
        $catName = strtolower($this->category->category_name ?? '');
        if ((int)$this->category_id !== 4 && !str_contains($catName, 'janitor')) {
            return false;
        }

        return !$this->is_manpower;
    }

    public function getManpowerDetailsAttribute(): ?array
    {
        if (!$this->is_manpower) {
            return null;
        }

        $desc = $this->description ?? '';
        if (str_starts_with(trim($desc), '{') && str_ends_with(trim($desc), '}')) {
            $data = json_decode($desc, true);
            if (is_array($data)) {
                return array_merge([
                    'activity_title' => $this->title,
                    'event_date' => '',
                    'venue' => $this->location,
                    'prep_date' => '',
                    'prep_details' => '',
                    'prep_regular' => true,
                    'prep_overtime' => false,
                    'prep_regular_time' => '8:00 - 12:00 / 1:00 - 5:00',
                    'prep_overtime_time' => '',
                    'assistance_date' => '',
                    'assistance_details' => '',
                    'assistance_regular' => true,
                    'assistance_overtime' => false,
                    'assistance_regular_time' => '8:00 - 12:00 / 1:00 - 5:00',
                    'assistance_overtime_time' => '',
                    'clearing_date' => '',
                    'clearing_details' => '',
                    'clearing_regular' => true,
                    'clearing_overtime' => false,
                    'clearing_regular_time' => '8:00 - 12:00 / 1:00 - 5:00',
                    'clearing_overtime_time' => '',
                    'additional_date' => '',
                    'additional_notes' => '',
                    'general_description' => '',
                ], $data);
            }
        }

        return [
            'activity_title' => $this->title,
            'event_date' => '',
            'venue' => $this->location,
            'prep_date' => '',
            'prep_details' => $desc,
            'prep_regular' => true,
            'prep_overtime' => false,
            'prep_regular_time' => '8:00 - 12:00 / 1:00 - 5:00',
            'prep_overtime_time' => '',
            'assistance_date' => '',
            'assistance_details' => '',
            'assistance_regular' => true,
            'assistance_overtime' => false,
            'assistance_regular_time' => '8:00 - 12:00 / 1:00 - 5:00',
            'assistance_overtime_time' => '',
            'clearing_date' => '',
            'clearing_details' => '',
            'clearing_regular' => true,
            'clearing_overtime' => false,
            'clearing_regular_time' => '8:00 - 12:00 / 1:00 - 5:00',
            'clearing_overtime_time' => '',
            'additional_date' => '',
            'additional_notes' => '',
            'general_description' => $desc,
        ];
    }

    public function getJanitorialDetailsAttribute(): ?array
    {
        if (!$this->is_janitorial) {
            return null;
        }

        $desc = $this->description ?? '';
        if (str_starts_with(trim($desc), '{') && str_ends_with(trim($desc), '}')) {
            $data = json_decode($desc, true);
            if (is_array($data) && (($data['type'] ?? '') === 'janitorial' || isset($data['janitorial_areas']))) {
                return $data;
            }
        }
        return null;
    }

    public function getDisplayDescriptionAttribute(): string
    {
        $desc = $this->description ?? '';
        if (str_starts_with(trim($desc), '{') && str_ends_with(trim($desc), '}')) {
            $data = json_decode($desc, true);
            if (is_array($data)) {
                // Non-Janitorial/Manpower requests should only show general description text
                if ((int)$this->category_id !== 4) {
                    return $data['general_description'] ?? (string)$desc;
                }

                if (($data['type'] ?? '') === 'janitorial' || isset($data['janitorial_areas'])) {
                    $lines = [];
                    if (!empty($data['janitorial_areas'])) $lines[] = "Target Area(s) / Rooms: " . $data['janitorial_areas'];
                    if (!empty($data['janitorial_type'])) $lines[] = "Scope of Cleaning: " . $data['janitorial_type'];
                    if (!empty($data['janitorial_frequency'])) $lines[] = "Preferred Frequency: " . $data['janitorial_frequency'];
                    if (!empty($data['janitorial_supplies'])) $lines[] = "Special Supplies / Equipment: " . $data['janitorial_supplies'];
                    if (!empty($data['general_description'])) $lines[] = "Additional Instructions: " . $data['general_description'];
                    return implode("\n\n", $lines);
                }

                $lines = [];
                if (!empty($data['activity_title'])) $lines[] = "Activity: " . $data['activity_title'];
                if (!empty($data['event_date'])) $lines[] = "Event Date: " . $data['event_date'];
                if (!empty($data['prep_details'])) {
                    $prepTime = (!empty($data['prep_regular']) ? 'Regular Time' : '') . (!empty($data['prep_overtime']) ? ' + Overtime' : '');
                    $lines[] = "Preparation (" . ($data['prep_date'] ?? 'N/A') . ($prepTime ? " | {$prepTime}" : '') . "): " . $data['prep_details'];
                }
                if (!empty($data['assistance_details'])) {
                    $assistTime = (!empty($data['assistance_regular']) ? 'Regular Time' : '') . (!empty($data['assistance_overtime']) ? ' + Overtime' : '');
                    $lines[] = "Event Assistance (" . ($data['assistance_date'] ?? 'N/A') . ($assistTime ? " | {$assistTime}" : '') . "): " . $data['assistance_details'];
                }
                if (!empty($data['clearing_details'])) {
                    $clearTime = (!empty($data['clearing_regular']) ? 'Regular Time' : '') . (!empty($data['clearing_overtime']) ? ' + Overtime' : '');
                    $lines[] = "Clearing / Teardown (" . ($data['clearing_date'] ?? 'N/A') . ($clearTime ? " | {$clearTime}" : '') . "): " . $data['clearing_details'];
                }
                if (!empty($data['additional_notes'])) $lines[] = "Additional Notes: " . $data['additional_notes'];
                if (!empty($data['general_description'])) $lines[] = $data['general_description'];
                return implode("\n\n", $lines);
            }
        }
        return (string)$desc;
    }
}
