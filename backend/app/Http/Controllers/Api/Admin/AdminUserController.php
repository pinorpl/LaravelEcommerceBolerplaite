<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Modules\UserManagement\Application\Queries\GetUsersQuery;
use Illuminate\Http\JsonResponse;

/**
 * Admin-only user management endpoints.
 */
class AdminUserController extends Controller
{
    public function __construct(
        private readonly GetUsersQuery $getUsersQuery
    ) {}

    /** GET /api/admin/users */
    public function index(): JsonResponse
    {
        $users = $this->getUsersQuery->execute(perPage: 20);
        return response()->json(UserResource::collection($users)->response()->getData(true));
    }
}
