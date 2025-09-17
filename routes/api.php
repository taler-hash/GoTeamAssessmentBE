<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return response()->json([
        'Hello' => 'World'
    ]);
});

//Auth Routes
Route::prefix('auth')
->controller(AuthController::class)
->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('/me', 'me');
        Route::post('/logout', 'logout');
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
