<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'resume_date' => 'date:Y-m-d',
        'is_half_day' => 'boolean',
        'duration_days' => 'float',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected $appends = [
        'leave_type_kh',
        'status_kh',
        'stage_kh',
    ];

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LeaveRequestApproval::class, 'leave_request_id')->orderBy('id', 'asc');
    }

    // --- Accessors ---

    public function getLeaveTypeKhAttribute(): string
    {
        return match ($this->leave_type) {
            'ANNUAL' => 'ច្បាប់ឈប់សម្រាកប្រចាំឆ្នាំ',
            'SHORT_TERM' => 'ច្បាប់ឈប់សម្រាករយៈពេលខ្លី',
            'MATERNITY' => 'ច្បាប់ឈប់សម្រាកលំហែមាតុភាព',
            'SICK' => 'ច្បាប់ឈប់សម្រាកព្យាបាលជំងឺ',
            'PERSONAL' => 'ច្បាប់ឈប់សម្រាកមានកិច្ចការផ្ទាល់ខ្លួន',
            default => $this->leave_type ?? 'មិនបញ្ជាក់',
        };
    }

    public function getStatusKhAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'សេចក្តីព្រាង',
            'PENDING' => 'កំពុងរង់ចាំពិនិត្យ',
            'APPROVED' => 'បានឯកភាព/អនុម័ត',
            'REJECTED' => 'បានបដិសេធ',
            'CANCELLED' => 'បានបោះបង់',
            default => $this->status ?? 'មិនបញ្ជាក់',
        };
    }

    public function getStageKhAttribute(): string
    {
        return match ($this->current_stage) {
            'OFFICE' => 'កម្រិតការិយាល័យ',
            'DEPARTMENT' => 'កម្រិតនាយកដ្ឋាន',
            'LEADERSHIP' => 'កម្រិតអគ្គនាយកដ្ឋាន',
            'COMPLETED' => 'បានបញ្ចប់',
            default => $this->current_stage ?? 'មិនបញ្ជាក់',
        };
    }
}
