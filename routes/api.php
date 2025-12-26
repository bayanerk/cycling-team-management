<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\RideParticipantController;
use App\Http\Controllers\RideTrackController;
use App\Http\Controllers\UserFitnessProfileController;
use App\Http\Controllers\UserLevelController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserSettingController;
use Illuminate\Support\Facades\Route;

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

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    // Registration
    Route::post('/register', [AuthController::class, 'register']);
    
    // Login
    Route::post('/login', [AuthController::class, 'login']);
    
    // OTP routes
    Route::post('/otp/send', [OtpController::class, 'sendOtp']);
    Route::post('/otp/verify', [OtpController::class, 'verifyOtp']);
});

// Protected routes (authentication required)
Route::middleware('auth:api')->prefix('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Get authenticated user
    Route::get('/me', [AuthController::class, 'me']);
});

// User Fitness Profile (protected)
Route::middleware('auth:api')->group(function () {
    Route::post('/users/fitness-profile', [UserFitnessProfileController::class, 'store']);
    Route::get('/users/me/fitness-profile', [UserFitnessProfileController::class, 'show']);
});

// User Addresses (protected)
Route::middleware('auth:api')->group(function () {
    Route::get('/users/me/addresses', [AddressController::class, 'index']);
    Route::post('/users/addresses', [AddressController::class, 'store']);
    Route::put('/users/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/users/addresses/{address}', [AddressController::class, 'destroy']);
});

// User Settings (protected)
Route::middleware('auth:api')->group(function () {
    Route::get('/users/me/settings', [UserSettingController::class, 'show']);
    Route::put('/users/me/settings', [UserSettingController::class, 'update']);
});

// User Level (protected)
Route::middleware('auth:api')->group(function () {
    Route::get('/users/me/level', [UserLevelController::class, 'show']);
});

// User Profile (protected)
Route::middleware('auth:api')->group(function () {
    Route::get('/users/me/profile', [UserProfileController::class, 'show']);
    Route::put('/users/me/profile', [UserProfileController::class, 'update']);
    Route::delete('/users/me/account', [UserProfileController::class, 'destroy']);
});

// Rides (Public listing for Home Screen)
Route::get('/rides', [RideController::class, 'index']);
Route::get('/rides/{ride}', [RideController::class, 'show']);

// Events (Public listing)
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

// Coaches (Public listing)
Route::get('/coaches', [CoachController::class, 'index']);
Route::get('/coaches/{coach}', [CoachController::class, 'show']);

// Memories (Public listing)
Route::get('/memories', [MemoryController::class, 'index']);
Route::get('/memories/{memory}', [MemoryController::class, 'show']);

// Rides (Admin only - protected)
Route::middleware('auth:api')->group(function () {
    Route::post('/rides', [RideController::class, 'store']);
    Route::put('/rides/{ride}', [RideController::class, 'update']);
    Route::delete('/rides/{ride}', [RideController::class, 'destroy']);
    
    // Events (Admin only)
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    
    // Coaches (Admin only)
    Route::post('/coaches', [CoachController::class, 'store']);
    Route::put('/coaches/{coach}', [CoachController::class, 'update']);
    Route::delete('/coaches/{coach}', [CoachController::class, 'destroy']);
    
    // Memories (Admin only)
    Route::get('/admin/memories', [MemoryController::class, 'indexAdmin']);
    Route::post('/memories', [MemoryController::class, 'store']);
    Route::put('/memories/{memory}', [MemoryController::class, 'update']);
    Route::delete('/memories/{memory}', [MemoryController::class, 'destroy']);
    
    // Ride Participants (User actions)
    Route::post('/rides/{ride}/join', [RideParticipantController::class, 'join']);
    Route::post('/ride-participants/{rideParticipant}/cancel', [RideParticipantController::class, 'cancel']);
    Route::get('/users/me/rides', [RideParticipantController::class, 'myRides']);
    Route::post('/ride-participants/{rideParticipant}/mark-completed', [RideParticipantController::class, 'markCompleted']);
    
    // Ride Tracks (GPS Tracking)
    Route::post('/ride-participants/{rideParticipant}/track', [RideTrackController::class, 'storeTrack']);
    Route::post('/ride-participants/{rideParticipant}/tracks', [RideTrackController::class, 'storeTracks']);
    Route::get('/ride-participants/{rideParticipant}/tracks', [RideTrackController::class, 'getTracks']);
    
    // Ride Participants (Admin only)
    Route::get('/rides/{ride}/participants', [RideParticipantController::class, 'getParticipants']);
    Route::post('/rides/{ride}/check-attendance', [RideParticipantController::class, 'checkAttendance']);
    Route::post('/ride-participants/{rideParticipant}/excuse', [RideParticipantController::class, 'excuse']);
});

