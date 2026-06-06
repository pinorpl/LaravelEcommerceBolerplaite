<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Modules\UserManagement\Application\Commands\RegisterUserCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AuthController handles register, login, logout.
 *
 * LOGIN NOTE: The actual OAuth token exchange happens via the Passport
 * /oauth/token endpoint (Password Grant), called by the Next.js API Route
 * /api/auth/login (server-side, keeping the client_secret private).
 * This controller only handles registration, logout, and fetching the user.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserCommand $registerUserCommand
    ) {}

    /**
     * POST /api/register
     *
     * Pipeline stages (via FormRequest validation):
     *   1. Validate input (RegisterRequest)
     *   2. Create user & assign 'buyer' role (RegisterUserCommand)
     *   3. Fire UserRegistered event (async welcome email)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerUserCommand->execute($request->validated());

        return response()->json([
            'message' => 'Registration successful. Welcome email is on its way!',
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * POST /api/logout
     * Revokes the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out.']);
    }

    /**
     * GET /api/user
     * Returns the authenticated user with their roles.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()->load('roles')));
    }
}
