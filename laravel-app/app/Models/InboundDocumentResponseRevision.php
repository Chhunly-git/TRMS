<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundDocumentResponseRevision extends Model
{
    use HasFactory;

    protected $table = 'inbound_document_response_revisions';

    protected $fillable = [
        'response_id',
        'user_id',
        'revision_number',
        'title',
        'header_organization',
        'reference_note',
        'content',
        'sign_title',
        'sign_name',
        'change_summary',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(InboundDocumentResponse::class, 'response_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
