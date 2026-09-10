<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'start_time',
        'end_time',
        'is_all_day',
        'location',
        'meeting_link',
        'description',
        'status',
        'priority',
        'color',
        'remind_at',
    ];

    protected $appends = [
        'type_khmer',
        'status_khmer',
        'priority_khmer',
        'start_datetime',
        'end_datetime',
        'venue',
        'all_day',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_all_day' => 'boolean',
            'remind_at' => 'datetime',
        ];
    }

    public function getStartDatetimeAttribute()
    {
        return $this->start_time ? $this->start_time->toIso8601String() : null;
    }

    public function getEndDatetimeAttribute()
    {
        return $this->end_time ? $this->end_time->toIso8601String() : null;
    }

    public function getVenueAttribute()
    {
        return $this->location;
    }

    public function getAllDayAttribute()
    {
        return (bool) $this->is_all_day;
    }

    /**
     * ទំនាក់ទំនងទៅកាន់ User (មន្ត្រីជាម្ចាស់កាលវិភាគ)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * បកប្រែប្រភេទកាលវិភាគជាភាសាខ្មែរ
     */
    protected function typeKhmer(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->type) {
                    'MEETING' => 'កិច្ចប្រជុំ',
                    'MISSION' => 'បេសកកម្ម',
                    'WORKSHOP' => 'សិក្ខាសាលា / វគ្គបណ្តុះបណ្តាល',
                    'TASK' => 'កិច្ចការងារទូទៅ',
                    'APPOINTMENT' => 'ការណាត់ជួប',
                    default => 'ផ្សេងៗ',
                };
            }
        );
    }

    /**
     * បកប្រែស្ថានភាពជាភាសាខ្មែរ
     */
    protected function statusKhmer(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'SCHEDULED' => 'គ្រោងទុក',
                    'IN_PROGRESS' => 'កំពុងដំណើរការ',
                    'COMPLETED' => 'បានបញ្ចប់',
                    'POSTPONED' => 'ពន្យារពេល',
                    'CANCELLED' => 'លុបចោល',
                    default => $this->status ?? 'គ្រោងទុក',
                };
            }
        );
    }

    /**
     * បកប្រែកម្រិតអាទិភាពជាភាសាខ្មែរ
     */
    protected function priorityKhmer(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->priority) {
                    'LOW' => 'ទាប',
                    'NORMAL' => 'ធម្មតា',
                    'HIGH' => 'សំខាន់',
                    'URGENT' => 'បន្ទាន់',
                    default => 'ធម្មតា',
                };
            }
        );
    }

    /**
     * Scope សម្រាប់ទាញយកកាលវិភាគក្នុងខែជាក់លាក់
     */
    public function scopeInMonth($query, $year, $month)
    {
        return $query->whereYear('start_time', $year)
                     ->whereMonth('start_time', $month);
    }

    /**
     * Scope សម្រាប់កាលវិភាគដែលជិតមកដល់ (Upcoming)
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now())
                     ->whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])
                     ->orderBy('start_time', 'asc');
    }
}
