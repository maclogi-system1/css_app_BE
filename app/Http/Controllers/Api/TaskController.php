<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

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
        $results = [];

        foreach ($request->post() as $index => $data) {
            if (! is_array($data)) {
                continue;
            }

            $result = $this->taskRepository->create($data, $storeId);

            if ($result?->has('errors')) {
                $status = $result->get('status');
                $errors[] = [
                    'index' => $index,
                    'row' => $index + 1,
                    'messages' => $result->get('errors'),
                ];
            }

            if ($result instanceof Collection && ! $result->has('errors')) {
                $results[] = $result->first();
            }
        }

        if (! empty($errors)) {
            return response()->json($errors, $status);
        }

        return ! empty($results)
            ? response()->json(['tasks' => $results], $status)
            : response()->json([
                'message' => __('Created failure.'),
            ]);
    }

    /**
     * Update multiple tasks in storage.
     */
    public function updateMultiple(Request $request, string $storeId)
    {
        $errors = [];
        $status = Response::HTTP_OK;
        $results = [];

        foreach ($request->post() as $index => $data) {
            if (! is_array($data)) {
                continue;
            }

            $result = $this->taskRepository->update($data, $storeId);

            if ($result?->has('errors')) {
                $status = $result->get('status');
                $errors[] = [
                    'index' => $index,
                    'row' => $index + 1,
                    'messages' => $result->get('errors'),
                ];
            }

            if ($result instanceof Collection && ! $result->has('errors')) {
                $results[] = $result->first();
            }
        }

        if (! empty($errors)) {
            return response()->json($errors, $status);
        }

        return ! empty($results)
            ? response()->json(['tasks' => $results], $status)
            : response()->json([
                'message' => __('Updated failure.'),
            ]);
    }

    /**
     * Delete a task.
     */
    public function delete(string $storeId, int $taskId)
    {
        $result = $this->taskRepository->delete($storeId, $taskId);
        if ($result?->has('errors')) {
            return response()->json($result->get('errors'), $result->get('status'));
        }

        return $result
            ? response()->json($result->get('data'), Response::HTTP_OK)
            : response()->json([
                'message' => __('Deleted failure.'),
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
