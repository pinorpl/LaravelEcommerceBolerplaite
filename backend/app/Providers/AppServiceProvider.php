<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Enable Password Grant — disabled by default in Passport 12+.
        // Required for the Next.js server-side token exchange flow.
        Passport::enablePasswordGrant();

        // Passport token expiration settings
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // NOTE: Passport::$hashesClientSecrets is false by default in this version.
        // Do NOT call Passport::hashClientSecrets() — it always enables hashing
        // regardless of any argument passed.
    }
}
