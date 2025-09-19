<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Services\TaskService;
use App\Http\Requests\AddTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected TaskService $taskService;
    
    private const ORIGINAL_DESCRIPTION = 'Original description';
    private const UPDATED_DESCRIPTION = 'Updated task description';

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskService = new TaskService();
    }

    public function test_create_task_success(): void
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Authenticate the user
        Auth::login($user);

        // Create a mock request
        $request = AddTaskRequest::create('/tasks', 'POST', [
            'date' => '2021-01-01',
            'description' => 'Test task description'
        ]);

        // Execute the method
        $result = $this->taskService->createTask($request);

        // Assertions
        $this->assertIsObject($result);
        $this->assertInstanceOf(\App\Http\Resources\TaskResource::class, $result);
        
        // Check if task was created in database
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'date' => '2021-01-01',
            'description' => 'Test task description'
        ]);
    }

    public function test_update_task_success(): void
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Create a task owned by the user
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'date' => '2021-01-01',
            'description' => self::ORIGINAL_DESCRIPTION
        ]);
        
        // Authenticate the user
        Auth::login($user);

        // Create a mock request
        $request = UpdateTaskRequest::create("/tasks/{$task->id}", 'PUT', [
            'date' => '2021-01-01',
            'description' => self::UPDATED_DESCRIPTION,
            'completed' => false
        ]);

        // Execute the method
        $result = $this->taskService->updateTask($request, $task->id);

        // Assertions
        $this->assertIsObject($result);
        $this->assertInstanceOf(\App\Http\Resources\TaskResource::class, $result);
        
        // Check if task was updated in database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $user->id,
            'date' => '2021-01-01',
            'description' => self::UPDATED_DESCRIPTION
        ]);
    }

    public function test_update_task_not_owned(): void
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Create a task owned by user1
        $task = Task::factory()->create([
            'user_id' => $user1->id,
            'date' => '2021-01-01',
            'description' => self::ORIGINAL_DESCRIPTION
        ]);
        
        // Authenticate user2 (not the owner)
        Auth::login($user2);

        // Create a mock request
        $request = UpdateTaskRequest::create("/tasks/{$task->id}", 'PUT', [
            'date' => '2021-01-01',
            'description' => self::UPDATED_DESCRIPTION,
            'completed' => false
        ]);

        // Execute the method - should return null since task is not owned by user2
        $result = $this->taskService->updateTask($request, $task->id);

        // Assertions
        $this->assertNull($result);
        
        // Check that task was not updated
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'date' => '2021-01-01',
            'description' => self::ORIGINAL_DESCRIPTION
        ]);
    }

    public function test_delete_task_success(): void
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'date' => '2021-01-01',
            'description' => 'Task to be deleted'
        ]);

        // Execute the method
        $result = $this->taskService->deleteTask($task->id);

        // Assertions
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
        
        // Check that task was soft deleted
        $this->assertSoftDeleted('tasks', [
            'id' => $task->id
        ]);
    }

    public function test_delete_task_not_found(): void
    {
        // Execute the method with non-existent task ID
        $result = $this->taskService->deleteTask('non-existent-id');

        // Assertions
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
        
        $responseData = json_decode($result->getContent(), true);
        $this->assertEquals('Task not found', $responseData['error']);
    }

    public function test_get_tasks_success(): void
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Create multiple tasks for the user
        Task::factory()->count(5)->create([
            'user_id' => $user->id,
            'date' => '2021-01-01',
        ]);
        
        // Create tasks for another user (should not be included)
        Task::factory()->count(3)->create([
            'user_id' => User::factory()->create()->id,
            'date' => '2021-01-01',
        ]);
        
        // Authenticate the user
        Auth::login($user);

        // Create a mock request
        $request = Request::create('/tasks', 'GET');

        // Execute the method
        $result = $this->taskService->getTasks($request);

        // Assertions
        $this->assertIsObject($result);
        $this->assertInstanceOf(\Illuminate\Http\Resources\Json\AnonymousResourceCollection::class, $result);
        
        // Check that only user's tasks are returned
        $this->assertCount(5, $result);
    }

    public function test_get_task_success(): void
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Create a task
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'date' => '2021-01-01',
            'description' => 'Test task'
        ]);

        // Create a mock request
        $request = Request::create("/tasks/{$task->id}", 'GET');

        // Execute the method
        $result = $this->taskService->getTask($request, $task->id);

        // Assertions
        $this->assertIsObject($result);
        $this->assertInstanceOf(\App\Http\Resources\TaskResource::class, $result);
    }

    public function test_get_task_not_found(): void
    {
        // Create a mock request with non-existent task ID
        $request = Request::create('/tasks/non-existent-id', 'GET');

        // Execute the method
        $result = $this->taskService->getTask($request, 'non-existent-id');

        // Assertions
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
        
        $responseData = json_decode($result->getContent(), true);
        $this->assertEquals('Task not found', $responseData['error']);
    }
}
