<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [];

    public function boot(): void
    {
        // Passport uses its own routes (oauth/token, oauth/authorize, etc.)
        // No need to call Passport::routes() in Laravel Passport 12+ with Laravel 11
    }
}
