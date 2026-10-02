<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            Log::warning('Potential SQL Injection attempt blocked', [
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
            '/\bUNION\s+(ALL\s+)?SELECT\b/i',
            '/\bSELECT\b.+?\bFROM\b/is',
            '/\bINSERT\s+INTO\b/i',
            '/\bUPDATE\s+[`\w\.\"]+\s+SET\b/i',
            '/\bDELETE\s+FROM\b/i',
            '/\bDROP\s+(TABLE|DATABASE|PROCEDURE|INDEX|VIEW|TRIGGER)\b/i',
            '/\bTRUNCATE\s+(TABLE\s+)?\w+\b/i',
            '/\bALTER\s+(TABLE|DATABASE)\b/i',
            '/\bCREATE\s+(TABLE|DATABASE|PROCEDURE|INDEX|VIEW|TRIGGER)\b/i',
            '/\b(EXEC|EXECUTE)\s+(xp_|sp_|\()/i',
            '/--[\s\r\n]+/',
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
