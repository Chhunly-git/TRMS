<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundDocumentMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inbound_document_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'comment',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $appends = [
        'action_kh',
    ];

    public function inboundDocument(): BelongsTo
    {
        return $this->belongsTo(InboundDocument::class, 'inbound_document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function actionKh(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->action) {
                    'REGISTERED' => 'ចុះលេខឯកសារចូល',
                    'FORWARDED_TO_ASSISTANT' => 'បញ្ជូនទៅជំនួយការអគ្គនាយក',
                    'ASSISTANT_RECEIVED' => 'ជំនួយការបានចុះលេខចូល និងកាលបរិច្ឆេទ',
                    'SUBMITTED_TO_DG' => 'ដាក់ជូនឯកឧត្តមអគ្គនាយក',
                    'DG_ANNOTATED' => 'ឯកឧត្តមអគ្គនាយកបានធ្វើចំណារ',
                    'DISPATCHED' => 'ចំណាត់ការរបស់ឯកឧត្តមអគ្គនាយក',
                    'FORWARDED' => 'ចាត់ចែង/បញ្ជូនបន្តតាមឋានានុក្រម',
                    'ACKNOWLEDGED' => 'ទទួលជ្រាប និងបញ្ចប់ដំណើរការ',
                    'RESPONSE_DRAFTED' => 'រៀបចំលិខិត',
                    'RESPONSE_SUBMITTED' => 'ដាក់ស្នើឆ្លងលិខិត/របាយការណ៍',
                    'RESPONSE_FORWARDED' => 'បញ្ជូនលិខិតបន្ត',
                    'RESPONSE_RETURNED' => 'បញ្ជូនត្រឡប់មកកែសម្រួលវិញ',
                    'RESPONSE_APPROVED' => 'ឯកឧត្តមអគ្គនាយកបានឯកភាព',
                    'COMPLETED' => 'បញ្ចប់ដំណើរការឯកសារជាស្ថាពរ',
                    'DOCUMENT_UPDATED' => 'បានកែប្រែព័ត៌មានលម្អិតឯកសារ',
                    'CANCELLED' => 'បានបោះបង់ឯកសារ',
                    default => $this->action,
                };
            }
        );
    }
}
