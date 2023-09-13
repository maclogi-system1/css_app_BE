<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {
    }

    /**
     * Get a listing of the task from oss api.
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->taskRepository->getList($request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Stores many newly created tasks in storage.
     */
    public function storeMultiple(Request $request, string $storeId)
    {
        $errors = [];
        $status = Response::HTTP_OK;

        foreach ($request->post() as $index => $data) {
            if (! is_array($data)) {
                continue;
            }

            $result = $this->taskRepository->create($data, $storeId);

            if ($result->has('errors')) {
                $status = $result->get('status');
                $errors[] = [
                    'index' => $index,
                    'row' => $index + 1,
                    'messages' => $result->get('errors'),
                ];
            }
        }

        if (! empty($errors)) {
            return response()->json($errors, $status);
        }

        return $result
            ? response()->json($result->get('data'), $status)
            : response()->json([
                'message' => __('Created failure.'),
            ]);
    }

    /**
     * Get a list of options for select.
     */
    public function getOptions()
    {
        $options = $this->taskRepository->getOptions();

        return response()->json($options);
    }
}
