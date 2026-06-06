<?php

namespace App\Modules\UserManagement\Application\Commands;

use App\Modules\UserManagement\Domain\Events\UserRegistered;
use App\Modules\UserManagement\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Event;

/**
 * Command (use-case action) for registering a new user.
 *
 * Part of the CQRS-lite approach: Commands mutate state (create user,
 * assign role, fire event). Queries only read.
 *
 * The Pipeline pattern is used in the Controller to pass the request
 * through validation stages before reaching this command.
 */
class RegisterUserCommand
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Execute the registration use case.
     * 1. Persist the user.
     * 2. Assign the default 'buyer' role.
     * 3. Fire the UserRegistered domain event (triggers welcome email via queue).
     */
    public function execute(array $data): \App\Modules\UserManagement\Domain\Models\User
    {
        $user = $this->userRepository->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // hashed automatically via User cast
        ]);

        $user->assignRole('buyer');

        Event::dispatch(new UserRegistered($user));

        return $user;
    }
}
