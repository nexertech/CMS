<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckPasswordRenewal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Only check for frontend_users (web) and houses (API)
        if (!$user || !($user instanceof \App\Models\FrontendUser || $user instanceof \App\Models\House)) {
            return $next($request);
        }

        $lastUpdate = $user->password_updated_at;

        if (!$lastUpdate) {
            // If for some reason it's null, set it to now and continue
            $user->update(['password_updated_at' => now()]);
            return $next($request);
        }

        $renewalDays = config('auth.password_renewal_days');
        $daysOld = $lastUpdate->diffInDays(now());

        // Hard Lock: Force password change after threshold + 5 days grace
        if ($daysOld >= ($renewalDays + 5)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password renewal required',
                    'update_required' => true,
                    'days_old' => $daysOld
                ], 403);
            }

            // Avoid infinite redirect if already on profile page or updating password
            if (!$request->is('user-profile*') && !$request->is('change-password*') && !$request->is('logout')) {
                return redirect()->route('frontend.profile')
                    ->with('warning', 'Your password has expired. Please update it to continue.');
            }
        }

        // Soft Reminder: Show alert if past threshold but within grace period
        if ($daysOld >= $renewalDays) {
            session()->flash('password_renewal_warning', "Your password is over $renewalDays days old. Please consider updating it for better security.");
        }

        return $next($request);
    }
}
