<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AuthMiddleware;

// API Routes 
Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {

    // Request not passed through middleware
    Route::post('/register-user', [AuthController::class, 'registerUser'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Request passed through middleware
    Route::middleware(['middleware' => 'auth'])->group(function () {
        Route::get('get-user-details', [AuthController::class, 'getUser']);
    });

});
