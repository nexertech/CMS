<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SqlInjectionMitigation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Recursively check all input for SQL injection patterns
        if ($this->containsSqlInjection($request->all())) {
            // Log the attempt for security auditing
            \Log::warning('Potential SQL Injection attempt blocked', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'input' => $request->except(['password', 'password_confirmation']),
            ]);

            // Implementation of generic error handling to avoid information leakage
            return response()->json([
                'error' => 'Your request could not be processed due to security policy violations.',
                'code' => 'SEC-SQLI-001'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Recursively check if the input contains SQL injection patterns.
     *
     * @param  mixed  $input
     * @return bool
     */
    private function containsSqlInjection($input): bool
    {
        if (is_array($input)) {
            foreach ($input as $value) {
                if ($this->containsSqlInjection($value)) {
                    return true;
                }
            }
            return false;
        }

        if (!is_string($input)) {
            return false;
        }

        // Define dangerous SQL patterns
        $patterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|TRUNCATE|ALTER|CREATE|EXEC|EXECUTE)\b\s+/i',
            '/--/',
            '/\/\*/',
            '/\bOR\b\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i',
            '/\bAND\b\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i',
            '/[\'"]\s*(OR|AND)\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i',
            '/\bWAITFOR\s+DELAY\b/i',
            '/\bSLEEP\(\d+\)/i',
            '/INFORMATION_SCHEMA/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
