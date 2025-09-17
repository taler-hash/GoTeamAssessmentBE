<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;

class AuthController extends Controller
{

    public function __construct(private AuthService $service)
    {
    }

    public function login(AuthLoginRequest $request)
    {
        $tokenWithUser = $this->service->authLogin($request);

        return response()->json([
            'message' => 'Login successful',
            'token' => $tokenWithUser->token,
            'user' => $tokenWithUser->user
        ]);
    }

    public function register(AuthRegisterRequest $request)
    {
        $tokenWithUser = $this->service->authRegister($request);

        return response()->json([
            'message' => 'Register successful',
            'token' => $tokenWithUser->token,
            'user' => $tokenWithUser->user
        ]);
    }

    public function logout(Request $request)
    {
        $this->service->authLogout($request);

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function me(Request $request)
    {
        $user = $this->service->authMe($request);

        return response()->json([
            'message' => 'Me successful',
            'user' => $user
        ]);
    }
}
