<?php

namespace App\WebServices;

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

    /**
     * Check mime type of a file is image.
     */
    public function isImage(UploadedFile $file): bool
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

    /**
     * Check mime type of a file is csv or plain text.
     */
    public function isTextCsv(UploadedFile $file): bool
    {
        return in_array($file->getClientMimeType(), [
            'text/csv',
            'text/plain',
        ]);
    }
}
