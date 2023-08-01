<?php

namespace App\Repositories\Contracts;

use App\Models\PolicyAttachment;
use Illuminate\Http\UploadedFile;

interface PolicyAttachmentRepository extends Repository
{
    /**
     * Handle uploaded file and create a new PolicyAttachment.
     */
    public function create(UploadedFile $file, string $attachmentKey): ?PolicyAttachment;

    /**
     * Handle a specified PolicyAttachment and remove file.
     */
    public function delete(PolicyAttachment $policyAttachment): ?PolicyAttachment;

    /**
     * Handle delete multiple attachment and remove file.
     */
    public function deleteMultiple(array $attachmentIds): ?bool;
}
