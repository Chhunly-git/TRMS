<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentNumberCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'issuing_unit_type',
        'issuing_unit_id',
        'year',
        'last_number',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_number' => 'integer',
    ];
}
