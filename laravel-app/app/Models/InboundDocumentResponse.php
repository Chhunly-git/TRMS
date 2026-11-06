<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DocumentWorkflowConfig;
use App\Models\User;
use App\Services\FileUrlService;

class InboundDocumentResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inbound_document_id',
        'response_type',
        'response_number',
        'number_seq',
        'number_year',
        'number_suffix',
        'issuing_unit_type',
        'issuing_unit_id',
        'title',
        'header_organization',
        'reference_note',
        'content',
        'sign_title',
        'sign_name',
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
        'number_seq' => 'integer',
        'number_year' => 'integer',
    ];

    protected $appends = [
        'status_kh',
        'stage_kh',
        'response_type_kh',
    ];

    public function inboundDocument(): BelongsTo
    {
        return $this->belongsTo(InboundDocument::class, 'inbound_document_id');
    }

    public function document(): BelongsTo
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

    public function workingGroup(): BelongsTo
    {
        return $this->belongsTo(WorkingGroup::class, 'issuing_unit_id');
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

    public function revisions(): HasMany
    {
        return $this->hasMany(InboundDocumentResponseRevision::class, 'response_id')->orderBy('revision_number', 'asc');
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

    protected function responseTypeKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->response_type) {
                    'REPORT' => 'របាយការណ៍',
                    'SUBMISSION_NOTE' => 'កំណត់បង្ហាញ',
                    'LETTER' => 'លិខិត',
                    default => 'របាយការណ៍',
                };
            }
        );
    }

    /**
     * Expand content with fresh signed URLs on render, and normalize/sanitize on save:
     * FileUrlService::normalizeContentForStorage -> HtmlSanitizerService::clean
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => FileUrlService::expandContentForRender($value, $this),
            set: fn (?string $value) => \App\Services\HtmlSanitizerService::clean(
                FileUrlService::normalizeContentForStorage($value)
            ),
        );
    }

    /**
     * Scope: ឯកសារចេញដែលអាចមើលឃើញដោយអ្នកប្រើប្រាស់ ស្របតាម DocumentWorkflowConfig (View Type)
     */
    public function scopeAccessibleByViewer($query, User $viewer)
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel <= 1);
        $isAssistant = method_exists($viewer, 'hasPermission') && $viewer->hasPermission('inbound-documents-assistant');

        // អគ្គនាយក (DG - Level 1), Admin និងជំនួយការអគ្គនាយក មើលឃើញទាំងអស់
        if ($isDg || $isAdmin || $isAssistant) {
            return $query;
        }

        // Check explicit DocumentWorkflowConfig (View Type)
        $wfConfig = DocumentWorkflowConfig::where('user_id', $viewer->id)->first();

        // ឯកសារពាក់ព័ន្ធនឹងខ្លួនផ្ទាល់ (Self Involvement)
        $selfInvolvement = function ($q) use ($viewer) {
            $q->where('drafted_by', $viewer->id)
              ->orWhere('current_approver_id', $viewer->id)
              ->orWhere('approved_by', $viewer->id)
              ->orWhereHas('approvals', fn($aq) => $aq->where('user_id', $viewer->id)->orWhere('forwarded_to_id', $viewer->id));
        };

        if ($wfConfig) {
            // 1. ALL: Full view across all outbound documents in system
            if ($wfConfig->view_type === 'ALL') {
                return $query;
            }

            // 2. SELF_ONLY: Only documents drafted, reviewed, or approved by the user
            if ($wfConfig->view_type === 'SELF_ONLY') {
                return $query->where($selfInvolvement);
            }

            // 3. DEPARTMENT: Can view all documents within their department
            if ($wfConfig->view_type === 'DEPARTMENT') {
                $deptIds = !empty($wfConfig->view_dept_ids) ? $wfConfig->view_dept_ids : array_filter([$viewer->department_id]);
                return $query->where(function ($q) use ($viewer, $deptIds, $selfInvolvement) {
                    $q->where($selfInvolvement);

                    if (!empty($deptIds)) {
                        $q->orWhereIn('department_id', $deptIds)
                          ->orWhereHas('draftedByUser', fn($uq) => $uq->whereIn('department_id', $deptIds))
                          ->orWhereHas('currentApprover', fn($uq) => $uq->whereIn('department_id', $deptIds));
                    }
                });
            }

            // 4. OFFICE: Can view all documents within their office
            if ($wfConfig->view_type === 'OFFICE') {
                $officeIds = !empty($wfConfig->view_office_ids) ? $wfConfig->view_office_ids : array_filter([$viewer->office_id]);
                return $query->where(function ($q) use ($viewer, $officeIds, $selfInvolvement) {
                    $q->where($selfInvolvement);

                    if (!empty($officeIds)) {
                        $q->orWhereIn('office_id', $officeIds)
                          ->orWhereHas('draftedByUser', fn($uq) => $uq->whereIn('office_id', $officeIds))
                          ->orWhereHas('currentApprover', fn($uq) => $uq->whereIn('office_id', $officeIds));
                    }
                });
            }

            // 5. SUBORDINATES: Can view own documents + subordinates in their unit
            if ($wfConfig->view_type === 'SUBORDINATES') {
                return $query->where(function ($q) use ($viewer, $viewerPosLevel, $selfInvolvement) {
                    $q->where($selfInvolvement)
                      ->orWhereHas('draftedByUser', function ($uq) use ($viewer, $viewerPosLevel) {
                          $uq->where(function ($sq) use ($viewer) {
                              if ($viewer->office_id) $sq->where('office_id', $viewer->office_id);
                              elseif ($viewer->department_id) $sq->where('department_id', $viewer->department_id);
                          })->whereHas('position', fn($pq) => $pq->where('level', '>', $viewerPosLevel));
                      });
                });
            }

            // 6. CUSTOM: Can view specific departments, offices, users, or working groups
            if ($wfConfig->view_type === 'CUSTOM') {
                $customDeptIds = $wfConfig->view_dept_ids ?: [];
                $customOfficeIds = $wfConfig->view_office_ids ?: [];
                $customUserIds = $wfConfig->view_user_ids ?: [];
                $customWgIds = $wfConfig->view_working_group_ids ?: [];

                return $query->where(function ($q) use ($customDeptIds, $customOfficeIds, $customUserIds, $customWgIds, $selfInvolvement) {
                    $q->where($selfInvolvement);

                    if (!empty($customDeptIds)) {
                        $q->orWhereIn('department_id', $customDeptIds)
                          ->orWhereHas('draftedByUser', fn($uq) => $uq->whereIn('department_id', $customDeptIds));
                    }
                    if (!empty($customOfficeIds)) {
                        $q->orWhereIn('office_id', $customOfficeIds)
                          ->orWhereHas('draftedByUser', fn($uq) => $uq->whereIn('office_id', $customOfficeIds));
                    }
                    if (!empty($customUserIds)) {
                        $q->orWhereIn('drafted_by', $customUserIds)
                          ->orWhereIn('current_approver_id', $customUserIds);
                    }
                    if (!empty($customWgIds)) {
                        $q->orWhereHas('inboundDocument.assignments', fn($aq) => $aq->whereIn('working_group_id', $customWgIds));
                    }
                });
            }
        }

        // Fallback default if not configured in DocumentWorkflowConfig
        // Dept Director / Deputy Director (level <= 5)
        if ($viewerPosLevel <= 5 && !empty($viewer->department_id)) {
            $deptId = $viewer->department_id;
            return $query->where(function ($q) use ($deptId, $selfInvolvement) {
                $q->where($selfInvolvement)
                  ->orWhere('department_id', $deptId)
                  ->orWhereHas('draftedByUser', fn($uq) => $uq->where('department_id', $deptId));
            });
        }

        // Office Chief / Deputy Chief (level <= 8)
        if ($viewerPosLevel <= 8 && !empty($viewer->office_id)) {
            $officeId = $viewer->office_id;
            return $query->where(function ($q) use ($officeId, $selfInvolvement) {
                $q->where($selfInvolvement)
                  ->orWhere('office_id', $officeId)
                  ->orWhereHas('draftedByUser', fn($uq) => $uq->where('office_id', $officeId));
            });
        }

        // General Officer
        return $query->where($selfInvolvement);
    }
}
