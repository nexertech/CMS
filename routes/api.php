<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ComplaintApiController;
use App\Http\Controllers\Api\HouseAuthController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\AppVersionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// DIAGNOSTIC ROUTE - Test database loading without auth (REMOVE AFTER DEBUGGING)
Route::get('/diagnostic/test-house', function () {
    try {
        $memStart = memory_get_usage();
        $house = \App\Models\House::first();
        $memAfter = memory_get_usage();
        
        return response()->json([
            'status' => 'SUCCESS',
            'house_id' => $house ? $house->id : null,
            'memory_start' => $memStart,
            'memory_after' => $memAfter,
            'memory_used' => $memAfter - $memStart,
            'message' => 'Database loading works fine'
        ]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Diagnostic Route Error: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json([
            'status' => 'ERROR',
            'message' => 'An error occurred during diagnostic test.'
        ], 500);
    }
});

// Public Authentication Routes (Strict rate limiting: max 5 login attempts per minute)
Route::middleware('throttle:5,1')->post('/house/login', [HouseAuthController::class, 'login']);

// Device Management Routes (Rate limiting: max 30 requests per minute)
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/check-device', [App\Http\Controllers\Api\DeviceController::class, 'checkDevice']);
    Route::post('/register-device', [App\Http\Controllers\Api\DeviceController::class, 'registerDevice']);
    Route::post('/get-device', [App\Http\Controllers\Api\DeviceController::class, 'getDevice']);
});

// Public Metadata Routes (Rate limiting: max 60 requests per minute)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/categories', [ComplaintApiController::class, 'categories']);
    Route::get('/categories/{category}/titles', [ComplaintApiController::class, 'getTitlesByCategory']);
    Route::get('/titles', [ComplaintApiController::class, 'titles']);
    Route::get('/app/check-update', [AppVersionController::class, 'checkUpdate']);
});

// DIAGNOSTIC: Test Sanctum Auth (REMOVE AFTER DEBUGGING)
Route::middleware('auth:sanctum')->get('/diagnostic/test-auth', function (Illuminate\Http\Request $request) {
    try {
        $user = $request->user();
        return response()->json([
            'status' => 'AUTH_SUCCESS',
            'user_id' => $user ? $user->id : null,
            'user_class' => $user ? get_class($user) : null,
            'message' => 'Sanctum authentication works'
        ]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Diagnostic Auth Error: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json([
            'status' => 'AUTH_ERROR',
            'message' => 'Authentication diagnostic error.'
        ], 500);
    }
});

// Protected Routes (Require Token & Rate Limited: max 60 requests per minute)
Route::middleware(['manual.auth', 'throttle:60,1'])->group(function () {
    Route::post('/house/logout', [HouseAuthController::class, 'logout']);
    Route::post('/house/change-password', [HouseAuthController::class, 'changePassword']);

    // Complaint Management
    Route::get('/complaints', [ComplaintApiController::class, 'index']); // Status/List
});

// FIXED: Use manual auth and strict rate limiting (max 15 requests per minute for complaint submission)
Route::middleware(['manual.auth', 'throttle:15,1'])->post('/complaints/register', [ComplaintApiController::class, 'register']);

// ... (debugging routes) ...

Route::middleware(['manual.auth', 'throttle:60,1'])->group(function () {
    Route::post('/complaints/{id}/feedback', [ComplaintApiController::class, 'feedback']); // Dynamic ID in URL
    Route::get('/complaints/{id}', [ComplaintApiController::class, 'show']); // Details

    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
});
