<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authService->register($request->validated());
            
            // Create token for the user
            $token = $user->createToken('auth-token')->plainTextToken;
            
            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Login user.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authService->login($request->email, $request->password);
            
            // Create token for the user
            $token = $user->createToken('auth-token')->plainTextToken;
            
            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Logout user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());
            
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}