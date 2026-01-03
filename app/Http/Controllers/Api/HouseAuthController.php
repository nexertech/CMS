<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\House;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class HouseAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
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

        $token = $house->createToken('house-app-token')->plainTextToken;

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
}
