<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_auth_login_success(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => Hash::make('password123')
        ]);

        // Create a mock request
        $request = Request::create('/login', 'POST', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        // Execute the method
        $result = $this->authService->authLogin($request);

        // Assertions
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('user', $result);
        $this->assertObjectHasProperty('token', $result);
        $this->assertInstanceOf(User::class, $result->user);
        $this->assertIsString($result->token);
        $this->assertEquals('testuser', $result->user->username);
    }

    public function test_auth_login_invalid_credentials(): void
    {
        // Create a test user
        User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('password123')
        ]);

        // Create a mock request with invalid credentials
        $request = Request::create('/login', 'POST', [
            'username' => 'testuser',
            'password' => 'wrongpassword'
        ]);

        // Expect AuthenticationException to be thrown
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->authLogin($request);
    }

    public function test_auth_register_success(): void
    {
        // Create a mock request
        $request = Request::create('/register', 'POST', [
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        // Execute the method
        $result = $this->authService->authRegister($request);

        // Assertions
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('user', $result);
        $this->assertObjectHasProperty('token', $result);
        $this->assertInstanceOf(User::class, $result->user);
        $this->assertIsString($result->token);
        $this->assertEquals('Test User', $result->user->name);
        $this->assertEquals('testuser', $result->user->username);
    }

    public function test_auth_logout(): void
    {
        // Create a test user
        $user = User::factory()->create();

        // Create a mock request with user
        $request = Request::create('/logout', 'POST');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Mock the currentAccessToken method to return a mock token
        $token = $this->createMock(\Laravel\Sanctum\PersonalAccessToken::class);
        $token->expects($this->once())->method('delete');
        
        $userMock = $this->partialMock(User::class, function ($mock) use ($token) {
            $mock->shouldReceive('currentAccessToken')->andReturn($token);
        });

        // Update the request to use our mocked user
        $request->setUserResolver(function () use ($userMock) {
            return $userMock;
        });

        // Execute the method
        $result = $this->authService->authLogout($request);

        // Assertions
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        
        $responseData = json_decode($result->getContent(), true);
        $this->assertEquals('Logout successful', $responseData['message']);
    }

    public function test_auth_me(): void
    {
        // Create a mock request
        $request = Request::create('/me', 'GET');

        // Execute the method - it should not throw an exception
        $result = $this->authService->authMe($request);

        // Assertions - auth()->user() might return null, which is expected
        $this->assertNull($result);
    }

    public function test_auth_logout_without_user(): void
    {
        // Create a mock request without user
        $request = Request::create('/logout', 'POST');
        $request->setUserResolver(function () {
            return null;
        });

        // Execute the method
        $result = $this->authService->authLogout($request);

        // Assertions
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        
        $responseData = json_decode($result->getContent(), true);
        $this->assertEquals('Logout successful', $responseData['message']);
    }
}