<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Forcefully remove header at PHP level before response is handled
        @header_remove('X-Powered-By');

        $response = $next($request);

        // Standard Security Headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'");
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Information Leakage Prevention
        $response->headers->remove('X-Powered-By');
        
        return $response;
    }
}
