<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundSenderOrganization extends Model
{
    use HasFactory;

    protected $table = 'inbound_sender_organizations';

    protected $fillable = [
        'name_kh',
        'name_en',
        'code',
        'category',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_active' => 'boolean',
    ];
}
