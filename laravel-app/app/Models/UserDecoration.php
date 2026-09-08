<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDecoration extends Model
{
    use HasFactory;

    protected $table = 'user_decorations';

    protected $fillable = [
        'user_id',
        'document_number',
        'date',
        'institution',
        'content',
        'type'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
