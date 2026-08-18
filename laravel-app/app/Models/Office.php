<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'code',
        'name_kh',
        'name_en',
    ];

    /**
     * ទំនាក់ទំនងទៅកាន់នាយកដ្ឋានសាមី (Department)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * ទំនាក់ទំនងទៅកាន់បុគ្គលិកចំណុះការិយាល័យ (Employees)
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}