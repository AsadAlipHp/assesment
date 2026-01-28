<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\ProfileController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index']);

    Route::get('/orders', [OrderController::class, 'index']); 
    Route::post('/orders', [OrderController::class, 'store']); 
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
});
