<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $isLocal = app()->environment('local');
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; object-src 'none'; font-src 'self' data: https://fonts.gstatic.com; connect-src 'self' https: wss: ws:;";

        if (method_exists($response, 'header')) {
            $response->header('X-Frame-Options', 'DENY');
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            if (!$isLocal) {
                $response->header('Content-Security-Policy', $csp);
            }
        } elseif (property_exists($response, 'headers')) {
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            if (!$isLocal) {
                $response->headers->set('Content-Security-Policy', $csp);
            }
        }

        return $response;
    }
}
