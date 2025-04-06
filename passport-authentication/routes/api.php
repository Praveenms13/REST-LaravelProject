<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController as AuthV1Controller; // Alias for V1
use App\Http\Controllers\Api\V2\AuthController as AuthV2Controller; // Alias for V2

// Prefix the routes with versioning for v1
Route::prefix('auth/v1')->group(function () {
    Route::post('/login', [AuthV1Controller::class, 'login']);
    Route::post('/register', [AuthV1Controller::class, 'register']);
    Route::post('/refresh', [AuthV1Controller::class, 'refreshToken']);

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('/me', [AuthV1Controller::class, 'me']);
        Route::post('/logout', [AuthV1Controller::class, 'logout']);
    });
});

// Prefix the routes with versioning for v2
Route::prefix('auth/v2')->group(function () {
    Route::post('/login', [AuthV2Controller::class, 'login']);
    Route::post('/register', [AuthV2Controller::class, 'register']);
    Route::post('/refresh', [AuthV2Controller::class, 'refreshToken']);

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('/me', [AuthV2Controller::class, 'me']);
        Route::post('/logout', [AuthV2Controller::class, 'logout']);
    });
});
