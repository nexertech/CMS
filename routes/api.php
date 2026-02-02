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

// Public Authentication Routes
Route::post('/house/login', [HouseAuthController::class, 'login']);

// Device Management Routes
Route::post('/check-device', [App\Http\Controllers\Api\DeviceController::class, 'checkDevice']);
Route::post('/register-device', [App\Http\Controllers\Api\DeviceController::class, 'registerDevice']);
Route::post('/get-device', [App\Http\Controllers\Api\DeviceController::class, 'getDevice']);

// Public Metadata Routes (No authentication required)
Route::get('/categories', [ComplaintApiController::class, 'categories']);
Route::get('/categories/{category}/titles', [ComplaintApiController::class, 'getTitlesByCategory']);
Route::get('/titles', [ComplaintApiController::class, 'titles']);
Route::get('/app/check-update', [AppVersionController::class, 'checkUpdate']);

// Protected Routes (Require Token)
// Route::middleware('auth:sanctum')->group(function () {
    Route::post('/house/logout', [HouseAuthController::class, 'logout']);
    Route::post('/house/change-password', [HouseAuthController::class, 'changePassword']);

    // Complaint Management
    Route::get('/complaints', [ComplaintApiController::class, 'index']); // Status/List
    Route::post('/complaints/register', [ComplaintApiController::class, 'register']); // Create
    Route::post('/complaints/{id}/feedback', [ComplaintApiController::class, 'feedback']); // Dynamic ID in URL
    Route::get('/complaints/{id}', [ComplaintApiController::class, 'show']); // Details

    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
// });
