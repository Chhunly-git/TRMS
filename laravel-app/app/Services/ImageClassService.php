<?php

namespace App\Services;

use App\Classes\ImageClass;

class ImageClassService
{
    public static function forUserModel(): ImageClass
    {
        return new ImageClass(StorageService::diskName(), 'users/profile-images', 128);
    }
}