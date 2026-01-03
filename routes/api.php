<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ComplaintApiController;
use App\Http\Controllers\Api\HouseAuthController;

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

// Protected Routes (Require Token)
// Route::middleware('auth:sanctum')->group(function () {
    Route::post('/house/logout', [HouseAuthController::class, 'logout']);

    // Complaint Management
    Route::get('/complaints', [ComplaintApiController::class, 'index']); // Status/List
    Route::post('/complaints/register', [ComplaintApiController::class, 'register']); // Create
    Route::post('/complaints/{id}/feedback', [ComplaintApiController::class, 'feedback']); // Dynamic ID in URL
    Route::get('/complaints/{id}', [ComplaintApiController::class, 'show']); // Details
    
    // Metadata
    Route::get('/categories', [ComplaintApiController::class, 'categories']);
    Route::get('/categories/{category}/titles', [ComplaintApiController::class, 'getTitlesByCategory']); // New cleaner route
    Route::get('/titles', [ComplaintApiController::class, 'titles']);
    
    
// });
