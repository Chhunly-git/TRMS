<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutboundDocumentInboundLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'outbound_document_id',
        'inbound_document_id',
    ];

    public function outboundDocument()
    {
        return $this->belongsTo(OutboundDocument::class);
    }

    public function inboundDocument()
    {
        return $this->belongsTo(InboundDocument::class);
    }
}
