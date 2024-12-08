<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TransactionController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/users/me', function (Request $request) {
        return $request->user();
    });

    Route::post('/transaction', [TransactionController::class, "store"]);

    Route::get('/history/singleplayer', [GameController::class, 'indexSinglePlayer']);
    Route::get('/history/multiplayer', [GameController::class, 'indexMultiPlayer']);
    
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::post('/games', [GameController::class, 'store']);
});

Route::post('/auth/login', [AuthController::class, "login"]);

// AFONSO
// Route::get('/games/{game}', [GameController::class, 'show']); nao foi preciso ate agora
Route::get('/boards', [BoardController::class, 'index']);
