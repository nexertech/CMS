<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

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
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net https://fonts.googleapis.com; font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; object-src 'none'; base-uri 'self'; form-action 'self'; frame-src 'none'; frame-ancestors 'self';");
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Information Leakage Prevention
        $response->headers->remove('X-Powered-By');

        // Enforce HttpOnly, Secure, and SameSite on ALL response cookies
        $this->enforceSecureCookies($response);
        
        return $response;
    }

    /**
     * Enforce security flags on all response cookies.
     * This ensures every cookie (session, theme, XSRF, etc.) has
     * HttpOnly, Secure, and SameSite attributes set correctly.
     */
    private function enforceSecureCookies(Response $response): void
    {
        $cookies = $response->headers->getCookies();
        
        foreach ($cookies as $cookie) {
            // Remove the original cookie
            $response->headers->removeCookie(
                $cookie->getName(),
                $cookie->getPath(),
                $cookie->getDomain()
            );

            // Re-add with enforced security flags
            $secureCookie = Cookie::create($cookie->getName())
                ->withValue($cookie->getValue())
                ->withExpires($cookie->getExpiresTime())
                ->withPath($cookie->getPath() ?: '/')
                ->withDomain($cookie->getDomain())
                ->withSecure(true)           // Always Secure
                ->withHttpOnly(true)         // Always HttpOnly
                ->withSameSite('Strict');     // Always SameSite=Strict

            $response->headers->setCookie($secureCookie);
        }
    }
}
