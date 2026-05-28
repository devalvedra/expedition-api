<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utils\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login and return a Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'iduser' => 'required|string',
            'pass'   => 'required|string',
        ]);

        if (!Auth::attempt(['iduser' => $request->iduser, 'password' => $request->pass])) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        /** @var User $user */
        $user = Auth::user();

        $token = $user->createToken('flutter_app')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user'  => $user,
        ], 'Login successful');
    }

    /**
     * Logout and revoke the current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request)
    {
        return ApiResponse::success($request->user(), 'Authenticated user');
    }
}
