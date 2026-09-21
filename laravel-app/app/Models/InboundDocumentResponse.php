<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InboundDocumentResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inbound_document_id',
        'response_number',
        'title',
        'content',
        'drafted_by',
        'department_id',
        'office_id',
        'file_path',
        'file_name',
        'current_approver_id',
        'current_stage',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'status_kh',
        'stage_kh',
    ];

    public function inboundDocument(): BelongsTo
    {
        return $this->belongsTo(InboundDocument::class, 'inbound_document_id');
    }

    public function draftedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'drafted_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(InboundDocumentResponseApproval::class, 'response_id')->orderBy('created_at', 'asc');
    }

    protected function statusKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'DRAFT' => 'ព្រាង',
                    'UNDER_REVIEW' => 'កំពុងពិនិត្យ/ឆ្លង',
                    'APPROVED_BY_DG' => 'ឯកភាពដោយអគ្គនាយក',
                    'RETURNED' => 'បញ្ជូនត្រឡប់',
                    default => $this->status,
                };
            }
        );
    }

    protected function stageKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->current_stage) {
                    'OFFICE' => 'ថ្នាក់ការិយាល័យ',
                    'DEPARTMENT' => 'ថ្នាក់នាយកដ្ឋាន',
                    'LEADERSHIP' => 'ថ្នាក់ដឹកនាំ (អគ្គនាយករង)',
                    'DG' => 'ឯកឧត្តមអគ្គនាយក',
                    'COMPLETED' => 'បញ្ចប់',
                    default => $this->current_stage,
                };
            }
        );
    }
}
