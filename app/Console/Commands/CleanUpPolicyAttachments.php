<?php

namespace App\Console\Commands;

use App\Models\PolicyAttachment;
use App\Repositories\Contracts\PolicyAttachmentRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanUpPolicyAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-up-policy-attachments
        {--all-public : Delete all files in public that have been created more than 1 day}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary uploaded policy attachments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $policyAttachments = PolicyAttachment::leftJoin('policies as p', 'p.id', '=', 'policy_attachments.policy_id')
            ->where(function ($query) {
                $query->whereNull('policy_attachments.policy_id')
                    ->orWhereNull('p.id');
            })
            ->where('created_at', '<', now()->subDay())
            ->get();
        $policyAttachmentRepository = $this->getRepository();

        foreach ($policyAttachments as $policyAttachment) {
            $policyAttachmentRepository->delete($policyAttachment);
        }

        if ($this->option('all-public')) {
            $this->deleteAllFiles();
        }

        return Command::SUCCESS;
    }

    /**
     * Get a instance of policy attachment repository.
     */
    protected function getRepository(): PolicyAttachmentRepository
    {
        return app(PolicyAttachmentRepository::class);
    }

    /**
     * Handles checking and deleting all files in public
     * that have been created more than 1 day without being linked to any policy.
     */
    protected function deleteAllFiles(): void
    {
        $disk = Storage::disk('public');
        $exceptionFiles = PolicyAttachment::all()->pluck('path')->toArray();
        $filePaths = array_diff($disk->allFiles(PolicyAttachment::IMAGE_PATH), $exceptionFiles);

        foreach ($filePaths as $filePath) {
            if ($disk->lastModified($filePath) <= now()->subDay()->getTimestamp()) {
                $disk->delete($filePath);
            }
        }
    }
}
