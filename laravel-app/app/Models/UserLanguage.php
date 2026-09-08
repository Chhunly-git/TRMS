<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLanguage extends Model
{
    use HasFactory;

    protected $table = 'user_languages';

    protected $fillable = [
        'user_id',
        'language',
        'reading',
        'writing',
        'speaking',
        'listening'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
