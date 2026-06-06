<?php

use App\Exceptions\ApiExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Detect locale from X-App-Locale header sent by the Next.js frontend.
        // This ensures validation messages are returned in the user's language.
        $middleware->prepend(\App\Http\Middleware\SetLocaleFromRequest::class);

        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Route all API exceptions through our safe, structured handler.
        // This prevents Laravel from ever leaking stack traces or SQL to the client.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->is('oauth/*') || $request->expectsJson()) {
                return app(ApiExceptionHandler::class)->render($request, $e);
            }
        });
    })->create();
