<?php

namespace App\Modules\UserManagement\Application\Queries;

use App\Modules\UserManagement\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Query handler for listing users with their roles.
 * In CQRS-lite, queries are read-only operations – they never mutate state.
 */
class GetUsersQuery
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginateWithRoles($perPage);
    }
}
