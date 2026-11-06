<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseOpinionPlacement extends Model
{
    use HasFactory;

    protected $table = 'response_opinion_placements';

    protected $fillable = [
        'response_id',
        'approval_id',
        'user_id',
        'slot',
        'order_seq',
        'movement_history',
    ];

    protected $casts = [
        'movement_history' => 'array',
        'order_seq' => 'integer',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(InboundDocumentResponse::class, 'response_id');
    }

    public function approval(): BelongsTo
    {
        return $this->belongsTo(InboundDocumentResponseApproval::class, 'approval_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
