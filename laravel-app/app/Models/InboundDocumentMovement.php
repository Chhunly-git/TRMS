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
                    'REGISTERED' => 'ចុះបញ្ជីទទួលឯកសារចូល',
                    'FORWARDED_TO_ASSISTANT' => 'បញ្ជូនទៅការិយាល័យអគ្គនាយក',
                    'ASSISTANT_RECEIVED' => 'ជំនួយការបានចុះលេខចូល និងកាលបរិច្ឆេទ',
                    'SUBMITTED_TO_DG' => 'ដាក់ជូនឯកឧត្តមអគ្គនាយក',
                    'DG_ANNOTATED' => 'ឯកឧត្តមអគ្គនាយកបានធ្វើចំណារ',
                    'DISPATCHED' => 'បាន Scan និងបញ្ជូនបន្តទៅអង្គភាព/មន្ត្រីទទួលបន្ទុក',
                    'FORWARDED' => 'ចាត់ចែង/បញ្ជូនបន្តតាមឋានានុក្រម',
                    'ACKNOWLEDGED' => 'ទទួលជ្រាប និងបញ្ចប់ដំណើរការ',
                    'RESPONSE_DRAFTED' => 'បានតាក់តែងលិខិតឆ្លើយតប',
                    'RESPONSE_FORWARDED' => 'បានបញ្ជូនលិខិតឆ្លើយតបឆ្លងតាមឋានានុក្រម',
                    'RESPONSE_RETURNED' => 'បានបញ្ជូនត្រឡប់មកកែសម្រួលវិញ',
                    'RESPONSE_APPROVED' => 'ឯកឧត្តមអគ្គនាយកបានឯកភាព/ចុះហត្ថលេខាលើលិខិតឆ្លើយតប',
                    'CANCELLED' => 'បានបោះបង់ឯកសារ',
                    default => $this->action,
                };
            }
        );
    }
}
