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
    ];

    protected $casts = ['submitted_at' => 'datetime'];

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
        return $this->hasMany(RequestHistory::class, 'request_id', 'request_id');
    }

    public function evaluation()
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
     * Check if this request is recurring (4+ requests with same/similar description within the same month)
     */
    public function getIsRecurringAttribute(): bool
    {
        return $this->recurring_count >= 4;
    }

    /**
     * Get total count of requests with the same or similar description in the same calendar month
     */
    public function getRecurringCountAttribute(): int
    {
        $rawDesc = trim($this->description ?? $this->title ?? '');
        if (!$this->submitted_at || empty($rawDesc)) {
            return 1;
        }

        // If JSON manpower details, get core text
        if (str_starts_with($rawDesc, '{') && str_ends_with($rawDesc, '}')) {
            $parsed = json_decode($rawDesc, true);
            $rawDesc = $parsed['general_description'] ?? $parsed['activity_title'] ?? $this->title ?? $rawDesc;
        }

        $date = $this->submitted_at;
        $cleanDesc = trim(strtolower($rawDesc));
        $prefix = substr($cleanDesc, 0, min(30, strlen($cleanDesc)));

        return static::whereMonth('submitted_at', $date->month)
            ->whereYear('submitted_at', $date->year)
            ->where(function($q) use ($cleanDesc, $prefix) {
                $q->whereRaw('LOWER(TRIM(description)) = ?', [$cleanDesc])
                  ->orWhereRaw('LOWER(TRIM(title)) = ?', [$cleanDesc]);
                if (strlen($prefix) >= 5) {
                    $q->orWhereRaw('LOWER(description) LIKE ?', ["%{$prefix}%"])
                      ->orWhereRaw('LOWER(title) LIKE ?', ["%{$prefix}%"]);
                }
            })
            ->count();
    }

    /**
     * Scope to filter recurring requests (where same/similar description appears >= 4 times in the same month)
     */
    public function scopeRecurring($query)
    {
        return $query->whereIn('request_id', function($sub) {
            $sub->select('r1.request_id')
                ->from('request as r1')
                ->join('request as r2', function($join) {
                    $join->whereRaw('MONTH(r1.submitted_at) = MONTH(r2.submitted_at)')
                         ->whereRaw('YEAR(r1.submitted_at) = YEAR(r2.submitted_at)')
                         ->whereRaw('(
                             LOWER(TRIM(r1.description)) = LOWER(TRIM(r2.description))
                             OR (LENGTH(r1.description) >= 6 AND LOWER(LEFT(TRIM(r1.description), 25)) = LOWER(LEFT(TRIM(r2.description), 25)))
                             OR (LENGTH(r1.title) >= 4 AND LOWER(TRIM(r1.title)) = LOWER(TRIM(r2.title)))
                         )');
                })
                ->groupBy('r1.request_id')
                ->havingRaw('COUNT(r2.request_id) >= 4');
        });
    }

    public function getManpowerDetailsAttribute(): array
    {
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

    public function getDisplayDescriptionAttribute(): string
    {
        $desc = $this->description ?? '';
        if (str_starts_with(trim($desc), '{') && str_ends_with(trim($desc), '}')) {
            $data = json_decode($desc, true);
            if (is_array($data)) {
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
