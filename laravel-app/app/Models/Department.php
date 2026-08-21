<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $guarded = []; // អនុញ្ញាតឱ្យ Insert ទិន្នន័យបានគ្រប់ Field

    // ទំនាក់ទំនងទៅកាន់ការិយាល័យ
    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    // ទំនាក់ទំនងទៅកាន់មន្ត្រី
   public function users()
{
    // តារាង users ឥឡូវនេះមាន department_id
    return $this->hasMany(User::class, 'department_id');
}
}