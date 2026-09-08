<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // កំណត់ថា Column ណាខ្លះអាច Insert ចូលបាន
    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'note'
    ];

    protected $appends = [
        'working_hours',
        'working_hours_formatted'
    ];

    // គណនាម៉ោងធ្វើការសរុប (ជាទម្រង់ Decimal ឧ. 8.5)
    public function getWorkingHoursAttribute()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        if (!in_array($this->status, ['PRESENT', 'LATE'])) {
            return 0;
        }

        try {
            $start = \Carbon\Carbon::parse($this->check_in_time);
            $end = \Carbon\Carbon::parse($this->check_out_time);

            if ($end->lessThanOrEqualTo($start)) {
                return 0;
            }

            $diffMinutes = $start->diffInMinutes($end);
            return round($diffMinutes / 60, 2);
        } catch (\Exception $e) {
            return null;
        }
    }

    // គណនាម៉ោងធ្វើការជាអក្សរខ្មែរ (ឧ. ៨ ម៉ោង ៣០ នាទី)
    public function getWorkingHoursFormattedAttribute()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        if (!in_array($this->status, ['PRESENT', 'LATE'])) {
            return null;
        }

        try {
            $start = \Carbon\Carbon::parse($this->check_in_time);
            $end = \Carbon\Carbon::parse($this->check_out_time);

            if ($end->lessThanOrEqualTo($start)) {
                return null;
            }

            $diffMinutes = $start->diffInMinutes($end);
            $hours = floor($diffMinutes / 60);
            $mins = $diffMinutes % 60;

            if ($mins === 0) {
                return "{$hours} ម៉ោង";
            }
            return "{$hours} ម៉ោង {$mins} នាទី";
        } catch (\Exception $e) {
            return null;
        }
    }

    // បង្កើតទំនាក់ទំនងទៅកាន់ User (មន្ត្រីម្នាក់)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}