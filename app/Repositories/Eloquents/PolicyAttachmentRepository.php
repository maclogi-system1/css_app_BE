<?php

namespace App\Repositories\Eloquents;

use App\Models\PolicyAttachment;
use App\Repositories\Contracts\PolicyAttachmentRepository as PolicyAttachmentRepositoryContract;
use App\Repositories\Repository;
use App\Services\UploadFileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PolicyAttachmentRepository extends Repository implements PolicyAttachmentRepositoryContract
{
    public function __construct(
        protected UploadFileService $uploadFileService
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return PolicyAttachment::class;
    }

    /**
     * Handle uploaded file and create a new PolicyAttachment.
     */
    public function create(UploadedFile $file, $attachmentKey): ?PolicyAttachment
    {
        return $this->handleSafely(function () use ($file, $attachmentKey) {
            $fileName = str($file->getClientOriginalName())
                ->snake()
                ->append('_' . time() . '.' . $file->extension());
            $pathUploadedFile = $this->uploadFileService->uploadImage(file: $file, dir: PolicyAttachment::IMAGE_PATH);

            $policy = $this->model()->fill([
                'attachment_key' => $attachmentKey,
                'name' => $fileName,
                'path' => $pathUploadedFile,
                'type' => PolicyAttachment::IMAGE_TYPE,
                'disk' => 'public',
            ]);
            $policy->save();

            return $policy;
        }, 'Create policy attachment');
    }

    /**
     * Handle a specified PolicyAttachment and remove file.
     */
    public function delete(PolicyAttachment $policyAttachment): ?PolicyAttachment
    {
        if (Storage::disk($policyAttachment->disk)->exists($policyAttachment->path)) {
            Storage::disk($policyAttachment->disk)->delete($policyAttachment->path);
        }

        $policyAttachment->delete();

        return $policyAttachment;
    }

    /**
     * Handle delete multiple policy attachment and remove file.
     */
    public function deleteMultiple(array $attachmentIds): ?bool
    {
        if (empty($attachmentIds)) {
            return null;
        }

        return $this->handleSafely(function () use ($attachmentIds) {
            $this->model()->whereIn('id', $attachmentIds)->get()->each(function ($policyAttachment) {
                if (Storage::disk($policyAttachment->disk)->exists($policyAttachment->path)) {
                    Storage::disk($policyAttachment->disk)->delete($policyAttachment->path);
                }

                $policyAttachment->delete();
            });

            return true;
        }, 'Delete multiple policy attachment');
    }
}
