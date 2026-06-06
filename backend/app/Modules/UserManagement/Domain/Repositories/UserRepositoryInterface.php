<?php

namespace App\Modules\UserManagement\Domain\Repositories;

use App\Modules\UserManagement\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Repository interface defined in the Domain layer.
 * The domain has no knowledge of Eloquent or any persistence technology –
 * it only knows about this contract. The Infrastructure layer provides
 * the concrete implementation (EloquentUserRepository).
 */
interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    /** Returns a paginated list of all users with their roles loaded. */
    public function paginateWithRoles(int $perPage = 15): LengthAwarePaginator;
}
