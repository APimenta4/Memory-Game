<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ScoreboardController;
use App\Http\Controllers\TransactionController;

// ## PUBLIC ##

Route::post('/auth/login', [AuthController::class, "login"]);

Route::get('/boards', [BoardController::class, 'index']);

// Scoreboard
Route::get('/scoreboards/global/multiplayer', [ScoreboardController::class, 'globalMultiplayerStatistics']);
Route::get('/scoreboards/global/singleplayer', [ScoreboardController::class, 'indexGlobalScoreboard']);


// AUTHENTICATED
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/me', [UserController::class, 'me']);

    // Games
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::post('/games', [GameController::class, 'store']);

    // History 
    Route::get('/history', [HistoryController::class, 'index']);
    Route::get('/history/singleplayer', [HistoryController::class, 'singleplayer']);
    Route::get('/history/multiplayer', [HistoryController::class, 'multiplayer']);
    
    // Scoreboard
    Route::get('/scoreboards/personal/singleplayer', [ScoreboardController::class, 'indexPersonalScoreboard']);
    Route::get('/scoreboards/personal/multiplayer', [ScoreboardController::class, 'personalMultiplayerStatistics']); 
});