<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutboundDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_number',
        'document_date',
        'document_time',
        'title',
        'document_type',
        'header_organization',
        'reference_note',
        'content',
        'sign_title',
        'sign_name',
        'file_path',
        'file_name',
        'created_by',
        'department_id',
        'office_id',
        'current_holder_id',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'document_date' => 'date:Y-m-d',
        'approved_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function linkedInboundDocuments()
    {
        return $this->belongsToMany(
            InboundDocument::class,
            'outbound_document_inbound_links',
            'outbound_document_id',
            'inbound_document_id'
        )->withTimestamps();
    }

    public function movements()
    {
        return $this->hasMany(OutboundDocumentMovement::class)->orderBy('id', 'desc');
    }
}
