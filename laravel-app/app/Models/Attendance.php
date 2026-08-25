<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // កំណត់ថា Column ណាខ្លះអាច Insert ចូលបាន
    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'note'
    ];

    // បង្កើតទំនាក់ទំនងទៅកាន់ User (មន្ត្រីម្នាក់)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}