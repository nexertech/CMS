<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\House;

class ManualTokenAuth
{
    /**
     * Handle an incoming request - manual token authentication
     * Bypasses Sanctum middleware which causes PHP crash
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated - No token provided'
            ], 401);
        }
        
        // Manual token lookup - avoid Sanctum middleware
        $personalAccessToken = PersonalAccessToken::findToken($token);
        
        if (!$personalAccessToken || !($personalAccessToken->tokenable_type === House::class || $personalAccessToken->tokenable_type === 'App\Models\House')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated - Invalid token'
            ], 401);
        }
        
        // Load house with only required columns
        $house = House::select('id', 'username', 'house_no', 'name', 'phone', 'city_id', 'sector_id', 'address', 'status', 'password_updated_at')
            ->find($personalAccessToken->tokenable_id);
        
        if (!$house) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated - House not found'
            ], 401);
        }
        
        // Attach house to request and ALSO set as authenticated user for the request
        $request->merge(['authenticated_house' => $house]);
        \Illuminate\Support\Facades\Auth::setUser($house);
        
        return $next($request);
    }
}
