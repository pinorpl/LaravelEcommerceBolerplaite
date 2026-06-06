<?php

namespace App\Modules\UserManagement\Application\Listeners;

use App\Modules\UserManagement\Domain\Events\UserRegistered;
use App\Modules\UserManagement\Domain\Models\User;
use App\Modules\UserManagement\Infrastructure\Mail\WelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Queued listener for sending welcome emails.
 *
 * [SEC] We store only the user ID in the queue payload (not the full model).
 * The model is re-fetched when the job executes. This prevents sensitive
 * data (hashed password, remember_token, relationships) from being serialized
 * into Redis.
 */
class SendWelcomeEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';
    public int $tries = 3;

    public function handle(UserRegistered $event): void
    {
        // [SEC] Re-fetch by ID — avoid serializing the full model into Redis
        $user = User::select('id', 'name', 'email')->find($event->user->id);

        if (! $user) {
            return; // User deleted between event dispatch and job execution
        }

        Mail::to($user->email)->send(new WelcomeMail($user));
    }
}
