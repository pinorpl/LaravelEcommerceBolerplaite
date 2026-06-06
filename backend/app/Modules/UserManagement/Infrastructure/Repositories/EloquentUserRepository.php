<?php

namespace App\Modules\UserManagement\Infrastructure\Repositories;

use App\Modules\UserManagement\Domain\Models\User;
use App\Modules\UserManagement\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent implementation of UserRepositoryInterface.
 * Lives in the Infrastructure layer – the only place that's allowed
 * to know about the ORM, SQL queries, etc.
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function paginateWithRoles(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('roles')->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
