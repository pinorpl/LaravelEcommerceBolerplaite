<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads the X-App-Locale header sent by Next.js and sets Laravel's app locale.
 * This makes validation messages, auth errors, etc. come back in the correct language.
 *
 * Supported locales: en, es
 * Fallback: en
 */
class SetLocaleFromRequest
{
    private const SUPPORTED = ['en', 'es'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('X-App-Locale', 'en');

        if (in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
