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
        $fileName ??= str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ->snake()
            ->append('_'.time().'.'.$file->extension());

        return $file->storeAs($dir, $fileName);
    }

    public function isImage(UploadedFile $file)
    {
        return in_array($file->getClientMimeType(), [
            'image/png',
            'image/jpeg',
            'image/bmp',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ]);
    }
}
