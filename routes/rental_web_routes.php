<?php
// ============================================================
//  RENTAL ADMIN ROUTES
//  Add these inside your existing routes/web.php (or general_web.php)
//  within the authenticated admin middleware group
// ============================================================

use Illuminate\Support\Facades\Route;

Route::get('rentals', function () {
    return view('rentals.index');
})->name('rentals.index');
