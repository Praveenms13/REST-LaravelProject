<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * User registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $userData = $request->validated();

        $userData['email_verified_at'] = now();
        $user = User::create($userData);

        // Preventing Logging in once registered
        // $response = Http::post(env('APP_URL') . '/oauth/token', [
        //     'grant_type' => 'password',
        //     'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
        //     'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
        //     'username' => $userData['email'],
        //     'password' => $userData['password'],
        //     'scope' => '',
        // ]);
        // $user['token'] = $response->json();
        // Preventing Logging in once registered - END

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'Version' => 'API Version 1',
            'message' => 'User has been registered successfully.',
            //'data' => $user,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ], 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            $response = Http::post(env('APP_URL') . '/oauth/token', [
                'grant_type' => 'password',
                'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
                'username' => $request->email,
                'password' => $request->password,
                'scope' => '',
            ]);
            $user['token'] = $response->json();
            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'Version' => 'API Version 1',
                'message' => 'User has been logged successfully.',
                'data' => $user,
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'statusCode' => 401,
                'message' => 'Unauthorized.',
                'errors' => 'Unauthorized',
                'Version' => 'Version 1',
            ], 401);
        }

    }

    /**
     * Login user
     *
     * @param  LoginRequest  $request
     */
    public function me(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = auth()->user();

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Authenticated user info.',
            'data' => $user,
        ], 200);
    }

    /**
     * refresh token
     *
     * @return void
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $response = Http::asForm()->post(env('APP_URL') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_SECRET'),
            'scope' => '',
        ]);
    
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'statusCode' => $response->status(), 
                'message' => 'Invalid refresh token.',
                'errors' => $response->json(), // Optional: include error details
            ], $response->status());
        }
    
        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Refreshed token.',
            'data' => $response->json(),
        ], 200);
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Logged out successfully.',
        ], 200);
    }
}
