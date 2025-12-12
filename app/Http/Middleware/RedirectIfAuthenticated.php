<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? ['web'] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // If accessing admin routes, redirect to admin dashboard
                if ($request->is('admin/*')) {
                    return redirect()->route('admin.dashboard');
                }
                // Otherwise redirect to frontend dashboard
                return redirect()->route('frontend.dashboard');
            }
        }

        // If not authenticated, allow access to login/register pages
        return $next($request);
    }
}

