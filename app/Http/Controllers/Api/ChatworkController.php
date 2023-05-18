<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Repositories\Contracts\ChatworkRepository;
use Illuminate\Http\JsonResponse;

class ChatworkController extends Controller
{
    public function __construct(
        private ChatworkRepository $chatworkRepository
    ) {}

    /**
     * Display a listing of the member.
     */
    public function membersList($roomId): JsonResponse
    {
        return response()->json([
            'room_id' => $roomId,
            'members' => $this->chatworkRepository->membersList($roomId),
        ]);
    }

    /**
     * Handle sending a message.
     */
    public function sendMessage(SendMessageRequest $request, $roomId): JsonResponse
    {
        $result = $this->chatworkRepository->sendMessage(
            $roomId,
            $request->input('message'),
            $request->input('send_to', [])
        );

        return $result ? response()->json([
            'message' => __('Message has been sent.'),
        ]) : response()->json([
            'message' => __('Message could not be sent.'),
        ]);
    }
}
