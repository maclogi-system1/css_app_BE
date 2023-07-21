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
    protected $signature = 'app:clean-up-policy-attachments';

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
        $policyAttachments = PolicyAttachment::whereNull('policy_id')
            ->where('created_at', '<', now()->subDay())
            ->get();
        $policyAttachmentRepository = $this->getRepository();

        foreach ($policyAttachments as $policyAttachment) {
            $policyAttachmentRepository->delete($policyAttachment);
        }
    }

    protected function getRepository(): PolicyAttachmentRepository
    {
        return app(PolicyAttachmentRepository::class);
    }
}
