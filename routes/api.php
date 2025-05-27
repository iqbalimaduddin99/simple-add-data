<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\Cors;
use App\Http\Controllers\NumbersController;



    Route::post('/fibonacci', [NumbersController::class, 'fibonacciSum']);
// Route::middleware([Cors::class])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
    
    Route::middleware('auth:sanctum')->get('categories', [CategoryController::class, 'index']);
    
    Route::middleware('auth:sanctum')->get('transactions/filter-data', [TransactionController::class, 'filter']);
    Route::middleware('auth:sanctum')->get('transactions/rekap', [TransactionController::class, 'rekap']);
    Route::middleware('auth:sanctum')->apiResource('transactions', TransactionController::class)->whereNumber('transaction');
// });
