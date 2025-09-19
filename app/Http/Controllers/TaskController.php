<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use App\Http\Requests\AddTaskRequest;
use App\Http\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        return $this->taskService->getTasks($request);
    }

    public function show(Request $request, string $id)
    {
        return $this->taskService->getTask($request, $id);
    }

    public function store(AddTaskRequest $request)
    {
        return response()->json([
            'message' => 'Task created successfully',
            'data' => $this->taskService->createTask($request),
        ]);
    }

    public function update(UpdateTaskRequest $request, string $id)
    {
        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $this->taskService->updateTask($request, $id),
        ]);
    }

    public function destroy(string $id)
    {
        return response()->json([
            'message' => 'Task deleted successfully',
            'data' => $this->taskService->deleteTask($id),
        ]);
    }
}
