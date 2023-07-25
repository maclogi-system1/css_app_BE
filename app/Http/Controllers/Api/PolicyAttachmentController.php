<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPolicyAttachmentRequest;
use App\Http\Resources\PolicyAttachmentResource;
use App\Models\PolicyAttachment;
use App\Repositories\Contracts\PolicyAttachmentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class PolicyAttachmentController extends Controller
{
    public function __construct(
        protected PolicyAttachmentRepository $policyAttachmentRepository
    ) {}

    /**
     * Generate a new attachment key for upload.
     */
    public function generateKey(): JsonResponse
    {
        return response()->json([
            'attachment_key' => str()->random(),
        ]);
    }

    /**
     * Handle uploaded file and create a new PolicyAttachment.
     */
    public function upload(UploadPolicyAttachmentRequest $request): JsonResource
    {
        $policyAttachment = $this->policyAttachmentRepository->create(
            $request->file('attachment'),
            $request->post('attachment_key')
        );

        return new PolicyAttachmentResource($policyAttachment);
    }

    /**
     * Handle a specified PolicyAttachment and remove file.
     */
    public function remove(PolicyAttachment $policyAttachment): JsonResource
    {
        $this->policyAttachmentRepository->delete($policyAttachment);

        return new PolicyAttachmentResource($policyAttachment);
    }

    /**
     * Remove multiple policy attachment at the same time.
     */
    public function removeMultiple(Request $request): JsonResponse
    {
        return ! $this->policyAttachmentRepository->deleteMultiple($request->query('attachment_ids', []))
            ? response()->json([
                'message' => __('Delete failed. Please check your attachment ids!'),
                'attachment_ids' => $request->query('attachment_ids', []),
            ], Response::HTTP_BAD_REQUEST)
            : response()->json([
                'message' => __('The policy attachment have been deleted successfully.'),
            ]);
    }
}
