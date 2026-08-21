<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $guarded = [];

    // ទំនាក់ទំនងត្រលប់ទៅកាន់នាយកដ្ឋានមេ
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // ទំនាក់ទំនងទៅកាន់មន្ត្រី
     public function users()
{
    // តារាង users ឥឡូវនេះមាន department_id
    return $this->hasMany(User::class, 'department_id');
}
}