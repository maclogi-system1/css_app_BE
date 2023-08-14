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
    ) {
    }

    /**
     * Get a specified room by room id.
     */
    public function roomDetail($roomId): JsonResponse
    {
        return response()->json([
            'room' => $this->chatworkRepository->roomDetail($roomId),
        ]);
    }

    /**
     * Display a listing of the member in the room.
     */
    public function roomMembers($roomId): JsonResponse
    {
        return response()->json([
            'room_id' => $roomId,
            'members' => $this->chatworkRepository->listMembersInRoom($roomId),
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
