<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController as FrontendHomeController;
use App\Http\Controllers\Frontend\AuthController as FrontendAuthController;

Route::get('/', [FrontendHomeController::class, 'index'])->name('frontend.home');
Route::get('/features', [FrontendHomeController::class, 'features'])->name('frontend.features');
Route::get('/dashboard', [FrontendHomeController::class, 'dashboard'])->middleware('auth:frontend')->name('frontend.dashboard');
Route::get('/user-profile', [FrontendHomeController::class, 'profile'])->middleware('auth:frontend')->name('frontend.profile');
// Route::get('/login', [FrontendAuthController::class, 'showLogin'])->name('frontend.login');
// Route::get('/register', [FrontendAuthController::class, 'showRegister'])->name('frontend.register');
Route::post('/login', [FrontendAuthController::class, 'login'])->name('frontend.login.post');
Route::post('/logout', [FrontendAuthController::class, 'logout'])->name('frontend.logout');
Route::post('/register', [FrontendAuthController::class, 'register'])->name('frontend.register.post');
Route::get('/forgot-password', [FrontendAuthController::class, 'showForgotPassword'])->name('frontend.forgot-password');

Route::middleware('auth:frontend')->group(function () {
    Route::post('/user-profile', [FrontendHomeController::class, 'updateProfile'])->name('frontend.profile.update');
    Route::get('/change-password', [FrontendHomeController::class, 'changePassword'])->name('frontend.password');
    Route::post('/change-password', [FrontendHomeController::class, 'updatePassword'])->name('frontend.password.update');
});



// Public Feedback Routes
Route::get('/complaint/{id}/feedback', [FrontendHomeController::class, 'feedback'])->name('frontend.feedback');
Route::post('/complaint/{id}/feedback', [FrontendHomeController::class, 'submitFeedback'])->name('frontend.feedback.submit');
