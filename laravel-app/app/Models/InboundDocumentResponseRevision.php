<?php

namespace App\Models;

use App\Services\FileUrlService;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * Rewrite legacy bucket URLs in content at render time without mutating the database
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => FileUrlService::rewriteLegacyContentUrls($value),
        );
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(InboundDocumentResponse::class, 'response_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
