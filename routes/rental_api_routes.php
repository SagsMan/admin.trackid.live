<?php
// ============================================================
//  RENTAL SERVICE ROUTES
//  Base prefix: /api/rentals
//  Add these inside your existing routes/api.php file
// ============================================================

use App\Http\Controllers\API\RentalController;
use App\Http\Controllers\API\RentalBookingController;
use Illuminate\Support\Facades\Route;

// Rental Categories
Route::get('rentals/categories', [RentalController::class, 'categories']);

// Rental Listings
Route::get('rentals',                          [RentalController::class, 'index']);
Route::get('rentals/{id}',                     [RentalController::class, 'show']);
Route::get('rentals/{id}/availability',        [RentalController::class, 'checkAvailability']);
Route::post('rentals',                         [RentalController::class, 'store']);
Route::put('rentals/{id}',                     [RentalController::class, 'update']);
Route::patch('rentals/{id}',                   [RentalController::class, 'update']);
Route::delete('rentals/{id}',                  [RentalController::class, 'destroy']);

// Rental Bookings
Route::post('rental-bookings',                            [RentalBookingController::class, 'store']);
Route::get('rental-bookings/{id}',                        [RentalBookingController::class, 'show']);
Route::get('rentals/{rentalId}/bookings',                 [RentalBookingController::class, 'byRental']);
Route::get('rental-bookings',                             [RentalBookingController::class, 'byRenter']);
Route::patch('rental-bookings/{id}/status',               [RentalBookingController::class, 'updateStatus']);
