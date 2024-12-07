<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\TransactionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/me', function (Request $request) {
        return $request->user();
    });

    Route::post('/transaction', [TransactionController::class, "store"]);
});

Route::post('/auth/login', [AuthController::class, "login"]);


// AFONSO
Route::get('/games/{game}', [GameController::class, 'show']);

Route::get('/users/me/history', [GameController::class, 'userHistory'])->middleware('auth:sanctum');

