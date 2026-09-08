<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserChild extends Model
{
    use HasFactory;

    protected $table = 'user_children';

    protected $fillable = [
        'user_id',
        'name',
        'latin_name',
        'gender',
        'dob',
        'occupation',
        'allowance'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
