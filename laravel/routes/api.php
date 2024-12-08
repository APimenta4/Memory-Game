<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/auth/login', [AuthController::class, "login"]);

// AFONSO
// Route::get('/games/{game}', [GameController::class, 'show']); nao foi preciso ate agora
Route::get('/boards', [BoardController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/me', [UserController::class, 'me']);

    // ------ History 
    Route::get('/users/me/history', [GameController::class, 'history']);  
    // ------ Scoreboard
    // singleplayer scoreboards
    Route::get('/scoreboard/personal/singleplayer', [GameController::class, 'indexPersonalScoreboard']);
    Route::get('/scoreboard/global/singleplayer', [GameController::class, 'indexGlobalScoreboard']);
    // multiplayer statistics
    Route::get('/scoreboard/personal/multiplayer', [GameController::class, 'personalMultiplayerStatistics']);
    Route::get('/scoreboard/global/multiplayer', [GameController::class, 'globalMultiplayerStatistics']); // esta também deve ser publica
})->middleware('auth:sanctum');

