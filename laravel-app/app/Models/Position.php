<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $guarded = [];

    // ទំនាក់ទំនងទៅកាន់មន្ត្រី
     public function users()
{
    // តារាង users ឥឡូវនេះមាន department_id
    return $this->hasMany(User::class, 'department_id');
}
}