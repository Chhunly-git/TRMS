<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['title_kh', 'title_en', 'level'];

    /**
     * ទំនាក់ទំនងទៅកាន់បុគ្គលិកដែលមានមុខតំណែងនេះ (Employees)
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}