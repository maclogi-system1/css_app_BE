<?php

namespace App\Repositories\Eloquents;

use App\Models\PolicyAttachment;
use App\Repositories\Contracts\PolicyAttachmentRepository as PolicyAttachmentRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\UploadFileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
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
                ->append('_'.time().'.'.$file->getClientOriginalExtension());
            $dir = PolicyAttachment::IMAGE_PATH;
            $type = PolicyAttachment::IMAGE_TYPE;

            if ($this->uploadFileService->isTextCsv($file)) {
                $dir = PolicyAttachment::TEXT_PATH;
                $type = PolicyAttachment::TEXT_TYPE;
            }

            $pathUploadedFile = $this->uploadFileService->uploadImage($file, $fileName, $dir);

            $policy = $this->model()->fill([
                'attachment_key' => $attachmentKey,
                'name' => $fileName,
                'path' => $pathUploadedFile,
                'type' => $type,
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
        $this->checkAndDeleteFile($policyAttachment);

        $policyAttachment->delete();

        return $policyAttachment;
    }

    /**
     * Check the files of the policy attachments in the disk and delete them.
     */
    private function checkAndDeleteFile(PolicyAttachment $policyAttachment): void
    {
        if (Storage::disk($policyAttachment->disk)->exists($policyAttachment->path)) {
            Storage::disk($policyAttachment->disk)->delete($policyAttachment->path);
        }
    }

    /**
     * Handle delete multiple policy attachment and remove file.
     */
    public function deleteMultiple(array|Collection $attachmentIds): ?bool
    {
        if (! count($attachmentIds)) {
            return null;
        }

        return $this->handleSafely(function () use ($attachmentIds) {
            $this->model()->whereIn('id', $attachmentIds)->get()->each(function ($policyAttachment) {
                $this->delete($policyAttachment);
            });

            return true;
        }, 'Delete multiple policy attachment');
    }
}
