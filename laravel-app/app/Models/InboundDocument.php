<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class InboundDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'general_inbound_number',
        'general_inbound_seq',
        'general_inbound_year',
        'received_date',
        'received_time',
        'deliverer_name',
        'deliverer_phone',
        'sender_organization',
        'external_reference_number',
        'external_document_date',
        'title',
        'document_type',
        'urgency',
        'confidentiality',
        'receptionist_notes',
        'original_file_path',
        'original_file_name',
        'registered_by',
        'dg_inbound_category',
        'dg_inbound_prefix',
        'dg_inbound_seq',
        'dg_inbound_year',
        'dg_inbound_number',
        'dg_received_date',
        'dg_assistant_id',
        'dg_assistant_notes',
        'dg_annotation',
        'dg_annotated_at',
        'is_response_required',
        'deadline',
        'annotated_file_path',
        'annotated_file_name',
        'dispatched_at',
        'dispatched_by',
        'target_type',
        'target_department_id',
        'target_office_id',
        'target_user_id',
        'dispatch_notes',
        'status',
        'acknowledged_at',
        'acknowledged_by',
        'completed_at',
    ];

    protected $casts = [
        'received_date' => 'date',
        'external_document_date' => 'date',
        'dg_received_date' => 'date',
        'deadline' => 'date',
        'dg_annotated_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_response_required' => 'boolean',
        'general_inbound_seq' => 'integer',
        'dg_inbound_seq' => 'integer',
    ];

    protected $appends = [
        'status_kh',
        'document_type_kh',
        'urgency_kh',
        'confidentiality_kh',
        'dg_category_kh',
        'deadline_status',
        'deadline_remaining_kh',
    ];

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dg_assistant_id');
    }

    public function dispatchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function targetDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'target_department_id');
    }

    public function targetOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'target_office_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InboundDocumentMovement::class, 'inbound_document_id')->orderBy('created_at', 'asc');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(InboundDocumentResponse::class, 'inbound_document_id')->orderBy('id', 'desc');
    }

    public function latestResponse(): HasOne
    {
        return $this->hasOne(InboundDocumentResponse::class, 'inbound_document_id')->latestOfMany();
    }

    // Accessors for Khmer presentation
    protected function statusKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'RECEPTION_DRAFT' => 'ទើបកត់ត្រា (មិនទាន់បញ្ជូន)',
                    'SUBMITTED_TO_ASSISTANT' => 'បានបញ្ជូនទៅជំនួយការ',
                    'SUBMITTED_TO_DG' => 'ដាក់ជូនអគ្គនាយក',
                    'DG_ANNOTATED' => 'មានចំណារអគ្គនាយក',
                    'DISPATCHED' => 'បានចែកចាយ',
                    'IN_RESPONSE_PROGRESS' => 'កំពុងរៀបចំឆ្លើយតប',
                    'COMPLETED' => 'បញ្ចប់រួចរាល់',
                    'CANCELLED' => 'បានបោះបង់',
                    default => $this->status ?? 'មិនស្គាល់',
                };
            }
        );
    }

    protected function documentTypeKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->document_type) {
                    'PRAKAS' => 'ប្រកាស',
                    'DECISION' => 'សេចក្តីសម្រេច',
                    'LETTER' => 'លិខិត',
                    'REPORT' => 'របាយការណ៍',
                    'INVITATION' => 'លិខិតអញ្ជើញ',
                    'OTHER' => 'ផ្សេងៗ',
                    default => $this->document_type ?? 'លិខិត',
                };
            }
        );
    }

    protected function urgencyKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->urgency) {
                    'NORMAL' => 'ធម្មតា',
                    'MEDIUM' => 'មធ្យម',
                    'URGENT' => 'បន្ទាន់',
                    'MOST_URGENT' => 'បន្ទាន់បំផុត',
                    default => 'ធម្មតា',
                };
            }
        );
    }

    protected function confidentialityKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->confidentiality) {
                    'NORMAL' => 'ធម្មតា',
                    'CONFIDENTIAL' => 'សម្ងាត់',
                    'TOP_SECRET' => 'សម្ងាត់បំផុត',
                    default => 'ធម្មតា',
                };
            }
        );
    }

    protected function dgCategoryKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->dg_inbound_category) {
                    'COMPANY' => 'ក្រុមហ៊ុន (AA)',
                    'MEF' => 'ក្រសួងសេដ្ឋកិច្ច (E)',
                    'FSA_REGULATOR' => 'អ.ស.ហ. និងនិយ័តករ (NF)',
                    'DEPT_GENERAL_AFFAIRS' => 'នាយកដ្ឋានកិច្ចការទូទៅ (A)',
                    'DEPT_REGISTRATION' => 'នាយកដ្ឋានចុះបញ្ជី (R)',
                    'DEPT_RESEARCH' => 'នាយកដ្ឋានស្រាវជ្រាវ (T)',
                    'DEPT_LEGAL' => 'នាយកដ្ឋានគតិយុត្ត (L)',
                    'PROJECT_ACSEP' => 'គម្រោង ACSEP (AS)',
                    default => $this->dg_inbound_category ?? 'ផ្សេងៗ',
                };
            }
        );
    }

    protected function deadlineStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->deadline) {
                    return 'NO_DEADLINE';
                }
                if ($this->status === 'COMPLETED' || $this->status === 'CANCELLED') {
                    return 'RESOLVED';
                }
                $today = Carbon::today();
                $dl = Carbon::parse($this->deadline)->startOfDay();
                if ($dl->lt($today)) {
                    return 'OVERDUE';
                }
                if ($dl->eq($today)) {
                    return 'TODAY';
                }
                $daysDiff = $today->diffInDays($dl);
                if ($daysDiff <= 3) {
                    return 'DUE_SOON';
                }
                return 'ON_TRACK';
            }
        );
    }

    protected function deadlineRemainingKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->deadline) {
                    return null;
                }
                if ($this->status === 'COMPLETED' || $this->status === 'CANCELLED') {
                    return 'បានបញ្ចប់ទាន់ពេល';
                }
                $today = Carbon::today();
                $dl = Carbon::parse($this->deadline)->startOfDay();
                if ($dl->lt($today)) {
                    $days = $today->diffInDays($dl);
                    return "ហួសកំណត់ {$days} ថ្ងៃ";
                }
                if ($dl->eq($today)) {
                    return 'ផុតកំណត់ថ្ងៃនេះ';
                }
                $days = $today->diffInDays($dl);
                return "នៅសល់ {$days} ថ្ងៃ";
            }
        );
    }
}
