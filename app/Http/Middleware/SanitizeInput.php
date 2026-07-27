<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Fields that should never be sanitized (passwords, tokens, file inputs).
     */
    protected array $exempt = [
        'password',
        'password_confirmation',
        '_token',
        'totp_code',
    ];

    /**
     * Handle an incoming request.
     * Strips HTML/PHP tags and trims whitespace from all string inputs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $clean = $this->sanitize($input);
        $request->merge($clean);

        return $next($request);
    }

    /**
     * Recursively sanitize an array of inputs.
     */
    protected function sanitize(array $input): array
    {
        foreach ($input as $key => $value) {
            if (in_array($key, $this->exempt)) {
                continue;
            }

            if (is_array($value)) {
                $input[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                // Strip HTML & PHP tags, then trim surrounding whitespace
                $input[$key] = trim(strip_tags($value));
            }
        }

        return $input;
    }
}
