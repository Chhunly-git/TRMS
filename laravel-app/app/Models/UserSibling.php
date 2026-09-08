<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSibling extends Model
{
    use HasFactory;

    protected $table = 'user_siblings';

    protected $fillable = [
        'user_id',
        'name',
        'latin_name',
        'gender',
        'dob',
        'occupation'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
