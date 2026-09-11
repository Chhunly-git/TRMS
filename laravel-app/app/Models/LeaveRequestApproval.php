<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestApproval extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'action_kh',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function forwardedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_to_id');
    }

    public function getActionKhAttribute(): string
    {
        return match ($this->action) {
            'SUBMITTED' => 'បានដាក់ពាក្យស្នើសុំ',
            'FORWARDED' => 'បានចារមតិ និងបញ្ជូនបន្ត',
            'APPROVED' => 'បានឯកភាព / អនុម័ត',
            'REJECTED' => 'បានបដិសេធ',
            'RETURNED' => 'បានប្រគល់ត្រឡប់',
            'CANCELLED' => 'បានបោះបង់',
            default => $this->action ?? 'មិនបញ្ជាក់',
        };
    }
}
