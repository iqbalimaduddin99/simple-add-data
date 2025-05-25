<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

// Route::apiResource('transactions', TransactionController::class);
// Route::get('transactions/filter', [TransactionController::class, 'filter']);