<?php

namespace App\Models;

use App\Services\StorageService;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * Get Full URL for certificate file
     */
    protected function certificateFile(): Attribute
    {
        return Attribute::make(
            get: function () {
                $file = $this->getRawOriginal('certificate_file');
                return $file ? StorageService::url($file) : null;
            }
        );
    }
}
