<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class UploadFileService
{
    /**
     * Handle upload image file and return path.
     */
    public function uploadImage(UploadedFile $file, $fileName = null, $dir = 'images'): string
    {
        $fileName ??= str($file->getClientOriginalName())
            ->snake()
            ->append('_'.time().'.'.$file->extension());

        return $file->storeAs($dir, $fileName);
    }
}
