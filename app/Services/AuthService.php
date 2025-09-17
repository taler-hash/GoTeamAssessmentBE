<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use App\Models\User;

class AuthService
{
  public function authLogin(Request $request)
  {
    $credentials = $request->only('username', 'password');
    $username = $credentials['username'] ?? '';
    $throttleKey = 'login_attempts:' . $username . '|' . $request->ip();

    $maxAttempts = 5;
    $decayMinutes = 1;

    if (cache()->has($throttleKey) && cache()->get($throttleKey) >= $maxAttempts) {
        throw new AuthenticationException('Too many login attempts. Please try again later.');
    }

    if (!Auth::attempt($credentials)) {
        cache()->increment($throttleKey, 1);
        cache()->put($throttleKey, cache()->get($throttleKey, 1), now()->addMinutes($decayMinutes));
        throw new AuthenticationException('Invalid credentials');
    }

    cache()->forget($throttleKey);

    $user = User::where('username', $username)->first();

    return (object)[
      'user' => $user,
      'token' => $user->createToken('api_token')->plainTextToken,
    ];
  }

  public function authRegister(Request $request)
  {
    return DB::transaction(function () use ($request) {
      $user = new User();
      $user->fill($request->all());
      $user->save();

      return (object)[
        'user' => $user,
        'token' => $user->createToken('api_token')->plainTextToken,
      ];
    });
  }

  public function authLogout(Request $request)
  {
    $user = $request->user();
    
    if ($user) {
      $user->currentAccessToken()->delete();
    }
    
    return response()->json([
      'message' => 'Logout successful'
    ]);
  }


  public function authMe(Request $request)
  {
    return auth()->user();
  }
}
