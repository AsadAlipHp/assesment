<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', function (Request $request) {
        return $request->user();
    });

    Route::get('/orders', [OrderController::class, 'index']); 
    Route::post('/orders', [OrderController::class, 'store']); 
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
});
