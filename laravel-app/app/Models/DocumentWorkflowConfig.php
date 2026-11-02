<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentWorkflowConfig extends Model
{
    use HasFactory;

    protected $table = 'document_workflow_configs';

    protected $fillable = [
        'user_id',
        'view_type',
        'view_dept_ids',
        'view_office_ids',
        'view_user_ids',
        'view_working_group_ids',
        'send_type',
        'send_dept_ids',
        'send_office_ids',
        'send_user_ids',
        'send_working_group_ids',
        'notes',
    ];

    protected $casts = [
        'view_dept_ids' => 'array',
        'view_office_ids' => 'array',
        'view_user_ids' => 'array',
        'view_working_group_ids' => 'array',
        'send_dept_ids' => 'array',
        'send_office_ids' => 'array',
        'send_user_ids' => 'array',
        'send_working_group_ids' => 'array',
    ];

    protected $appends = [
        'view_type_kh',
        'send_type_kh',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getViewTypeKhAttribute(): string
    {
        return match ($this->view_type) {
            'ALL' => 'ទាំងអស់ (គ្រប់ឯកសារក្នុងប្រព័ន្ធ)',
            'DEPARTMENT' => 'ក្នុងនាយកដ្ឋាន',
            'OFFICE' => 'ក្នុងការិយាល័យ',
            'SUBORDINATES' => 'មន្ត្រីក្រោមឱវាទ & ខ្លួនឯង',
            'SELF_ONLY' => 'បុគ្គល (តែឯកសារផ្ទាល់ខ្លួន)',
            'CUSTOM' => 'កំណត់ដោយដៃ (Custom)',
            default => $this->view_type ?? 'មន្ត្រីក្រោមឱវាទ & ខ្លួនឯង',
        };
    }

    public function getSendTypeKhAttribute(): string
    {
        return match ($this->send_type) {
            'ALL' => 'ទាំងអស់ (អាចផ្ញើទៅគ្រប់អង្គភាព/មន្ត្រី)',
            'DEPARTMENT' => 'ក្នុងនាយកដ្ឋាន',
            'OFFICE' => 'ក្នុងការិយាល័យ',
            'SUBORDINATES' => 'មន្ត្រីក្រោមឱវាទតាមឋានានុក្រម',
            'CUSTOM' => 'កំណត់ដោយដៃ (Custom)',
            default => $this->send_type ?? 'មន្ត្រីក្រោមឱវាទតាមឋានានុក្រម',
        };
    }
}
