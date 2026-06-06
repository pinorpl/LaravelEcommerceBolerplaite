<?php

namespace App\Providers;

use App\Modules\UserManagement\Domain\Events\UserRegistered;
use App\Modules\UserManagement\Application\Listeners\SendWelcomeEmailListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Maps domain events to their listeners.
     * UserRegistered → SendWelcomeEmailListener (queued, sends via Redis queue)
     */
    protected $listen = [
        UserRegistered::class => [
            SendWelcomeEmailListener::class,
        ],
    ];

    public function boot(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
