<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEducation extends Model
{
    use HasFactory;

    protected $table = 'user_educations';

    protected $fillable = [
        'user_id',
        'category',
        'course_level',
        'institution',
        'degree',
        'start_date',
        'end_date',
        'certificate_file'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
