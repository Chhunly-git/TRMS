<?php

namespace App\Models;

use App\Services\FileUrlService;
use App\Services\StorageService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $table = 'document_templates';

    protected $fillable = [
        'title',
        'category',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get full URL for template file
     */
    protected function filePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                $file = $this->getRawOriginal('file_path');
                if (empty($file)) {
                    return null;
                }

                $viewer = auth()->user() ?? request()?->user();
                if (!$viewer) {
                    return null;
                }

                return FileUrlService::signed($file, 30);
            }
        );
    }
}
