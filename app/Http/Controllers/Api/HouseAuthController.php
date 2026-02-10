<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\House;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\LoginHistory;

class HouseAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'fcm_token' => 'nullable|string|max:255',
        ]);

        $house = House::where('username', $request->username)->first();

        if (! $house || ! Hash::check($request->password, $house->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if ($house->status !== 'active') {
             return response()->json([
                'success' => false,
                'message' => 'Account is inactive'
            ], 403);
        }

        // Delete existing tokens if you want single session, or keep them
        // $house->tokens()->delete();

        // Update FCM token if provided
        if ($request->filled('fcm_token')) {
            $house->fcm_token = $request->fcm_token;
            $house->save();
        }

        $token = $house->createToken('house-app-token')->plainTextToken;

        LoginHistory::create([
            'user_id' => $house->id,
            'user_type' => get_class($house),
            'username' => $house->username,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'app',
        ]);


        $renewalDays = config('auth.password_renewal_days');
        $daysOld = $house->password_updated_at ? $house->password_updated_at->diffInDays(now()) : 0;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $house->id,
                'username' => $house->username,
                'house_no' => $house->house_no,
                'name' => $house->name,
                'city_id' => $house->city_id,
                'sector_id' => $house->sector_id,
                'password_age' => $daysOld,
                'unit' => 'days',
                'password_renewal_required' => $daysOld >= $renewalDays,
                'password_hard_locked' => $daysOld >= ($renewalDays + 5),
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Change password for authenticated house
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|same:new_password',
        ]);

        $house = $request->user();

        // Manual token check if middleware doesn't auto-resolve
        if (!$house) {
            $token = $request->bearerToken();
            if ($token) {
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($personalAccessToken && $personalAccessToken->tokenable_type === House::class) {
                    $house = $personalAccessToken->tokenable;
                }
            }
        }

        if (!$house) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $house->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Update password
        $house->password = $request->new_password;
        $house->password_updated_at = now();
        $house->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
