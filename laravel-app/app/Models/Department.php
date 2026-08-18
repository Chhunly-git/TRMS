<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name_kh', 'name_en', 'is_active'];

    /**
     * ទំនាក់ទំនងទៅកាន់ការិយាល័យចំណុះ (Offices)
     */
    public function offices(): HasMany
    {
        return $this->hasMany(Office::class);
    }

    /**
     * ទំនាក់ទំនងទៅកាន់បុគ្គលិកចំណុះនាយកដ្ឋាន (Employees)
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}