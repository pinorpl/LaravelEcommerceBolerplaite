<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Centralised API error handler.
 *
 * Goals:
 *  1. Return a consistent JSON shape: { error, message, errors? }
 *  2. Never expose stack traces, file paths, SQL or internal details in production.
 *  3. Use error CODES so the frontend can translate them independently.
 *
 * Error shape:
 *  {
 *    "error":   "VALIDATION_ERROR",      // machine-readable code
 *    "message": "The given data was invalid.",  // safe human message
 *    "errors":  { "email": ["..."] }     // only for validation failures
 *  }
 */
class ApiExceptionHandler
{
    public function render(Request $request, Throwable $e): JsonResponse
    {
        // ── Validation (422) ─────────────────────────────────────────────────
        if ($e instanceof ValidationException) {
            return response()->json([
                'error'   => 'VALIDATION_ERROR',
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }

        // ── Unauthenticated (401) ─────────────────────────────────────────────
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'error'   => 'UNAUTHENTICATED',
                'message' => 'Authentication required.',
            ], 401);
        }

        // ── Forbidden (403) ───────────────────────────────────────────────────
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'error'   => 'FORBIDDEN',
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        // ── Model not found / route not found (404) ───────────────────────────
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'error'   => 'NOT_FOUND',
                'message' => 'The requested resource was not found.',
            ], 404);
        }

        // ── Generic HTTP exceptions (405, 429, etc.) ──────────────────────────
        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $code   = match($status) {
                405    => 'METHOD_NOT_ALLOWED',
                429    => 'TOO_MANY_REQUESTS',
                503    => 'SERVICE_UNAVAILABLE',
                default => 'HTTP_ERROR',
            };
            return response()->json([
                'error'   => $code,
                'message' => $this->safeHttpMessage($status),
            ], $status);
        }

        // ── Unhandled server error (500) ──────────────────────────────────────
        // NEVER expose internal details (stack trace, SQL, file paths) outside debug mode.
        $debug = config('app.debug') && config('app.env') !== 'production';

        return response()->json([
            'error'   => 'SERVER_ERROR',
            'message' => 'An unexpected error occurred. Please try again later.',
            // Only include technical details in local/development debug mode
            'detail'  => $debug ? $e->getMessage() : null,
        ], 500);
    }

    private function safeHttpMessage(int $status): string
    {
        return match($status) {
            400 => 'Bad request.',
            405 => 'Method not allowed.',
            429 => 'Too many requests. Please slow down.',
            503 => 'Service temporarily unavailable.',
            default => 'An HTTP error occurred.',
        };
    }
}
