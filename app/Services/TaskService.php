<?php

namespace App\Services;

use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TaskService
{
    public function createTask(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $task =(new Task())->fill($request->all());
            $task->user_id = auth()->user()->id;
            $task->save();

            return new TaskResource($task);
        });
    }

    public function updateTask(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $task = auth()->user()->tasks()->find($id);
            
            if (!$task) {
                return null;
            }
            
            $task->fill($request->all());
            $task->save();

            return new TaskResource($task);
        });
    }

    public function deleteTask(string $id)
    {
        return DB::transaction(function () use ($id) {
            $task = Task::find($id);
            
            if (!$task) {
                return response()->json(['error' => 'Task not found'], 404);
            }
            
            $task->delete();

            return response()->noContent();
        });
    }

    public function getTasks(Request $request)
    {
       $tasks = (new Task())->owned()->paginate(10);

        return TaskResource::collection($tasks);
    }

    public function getTask(Request $request, string $id)
    {
        $task = Task::find($id);
        
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }
        
        return new TaskResource($task);
    }
}
