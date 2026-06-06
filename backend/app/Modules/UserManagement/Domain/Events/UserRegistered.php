<?php

namespace App\Modules\UserManagement\Domain\Events;

use App\Modules\UserManagement\Domain\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain event fired when a new user successfully completes registration.
 * Listeners (e.g. SendWelcomeEmailListener) react to this event asynchronously
 * via the Redis queue, keeping the registration pipeline fast.
 */
class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}
}
