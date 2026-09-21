<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundDocumentResponseApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'user_id',
        'action',
        'stage',
        'comment',
        'attachment_path',
        'attachment_name',
        'forwarded_to_id',
    ];

    protected $appends = [
        'action_kh',
        'stage_kh',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(InboundDocumentResponse::class, 'response_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function forwardedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_to_id');
    }

    protected function actionKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->action) {
                    'SUBMIT' => 'ដាក់ស្នើឆ្លង',
                    'FORWARD' => 'បញ្ជូនបន្ត',
                    'RETURN' => 'បញ្ជូនត្រឡប់',
                    'DG_APPROVE' => 'ឯកភាព/ចុះហត្ថលេខា',
                    default => $this->action,
                };
            }
        );
    }

    protected function stageKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->stage) {
                    'OFFICE' => 'ថ្នាក់ការិយាល័យ',
                    'DEPARTMENT' => 'ថ្នាក់នាយកដ្ឋាន',
                    'LEADERSHIP' => 'ថ្នាក់ដឹកនាំ (អគ្គនាយករង)',
                    'DG' => 'ឯកឧត្តមអគ្គនាយក',
                    default => $this->stage,
                };
            }
        );
    }
}
