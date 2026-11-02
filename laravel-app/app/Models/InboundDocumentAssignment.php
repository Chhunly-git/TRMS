<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InboundDocumentAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inbound_document_id',
        'assigned_by',
        'parent_assignment_id',
        'target_type',
        'department_id',
        'office_id',
        'user_id',
        'working_group_id',
        'instruction',
        'status',
        'acknowledged_at',
        'completed_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'target_label_kh',
        'target_type_kh',
        'status_kh',
    ];

    public function inboundDocument(): BelongsTo
    {
        return $this->belongsTo(InboundDocument::class, 'inbound_document_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function parentAssignment(): BelongsTo
    {
        return $this->belongsTo(InboundDocumentAssignment::class, 'parent_assignment_id');
    }

    public function childAssignments(): HasMany
    {
        return $this->hasMany(InboundDocumentAssignment::class, 'parent_assignment_id')->with(['user', 'office', 'department']);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workingGroup(): BelongsTo
    {
        return $this->belongsTo(WorkingGroup::class, 'working_group_id');
    }

    protected function targetTypeKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->target_type) {
                    'DEPARTMENT' => 'នាយកដ្ឋាន',
                    'OFFICE' => 'ការិយាល័យ',
                    'USER' => 'មន្ត្រី/បុគ្គល',
                    'WORKING_GROUP' => 'ក្រុមការងារ',
                    default => $this->target_type,
                };
            }
        );
    }

    protected function targetLabelKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->target_type === 'WORKING_GROUP' && $this->workingGroup) {
                    return $this->workingGroup->name_kh ?? $this->workingGroup->name_en ?? 'ក្រុមការងារ';
                }
                if ($this->target_type === 'DEPARTMENT' && $this->department) {
                    return $this->department->name_kh ?? $this->department->name ?? 'នាយកដ្ឋាន';
                }
                if ($this->target_type === 'OFFICE' && $this->office) {
                    return $this->office->name_kh ?? $this->office->name ?? 'ការិយាល័យ';
                }
                if ($this->target_type === 'USER' && $this->user) {
                    return $this->user->name_kh ?? $this->user->name ?? 'មន្ត្រី';
                }
                return 'មិនកំណត់';
            }
        );
    }

    protected function statusKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'PENDING' => 'រង់ចាំពិនិត្យ',
                    'ACKNOWLEDGED' => 'បានទទួលជ្រាប',
                    'IN_PROGRESS' => 'កំពុងចាត់ចែង/រៀបចំ',
                    'COMPLETED' => 'បានបញ្ចប់',
                    default => $this->status,
                };
            }
        );
    }
}
