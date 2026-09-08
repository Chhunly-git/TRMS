<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateWorkHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'company',
        'position',
        'skill',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
