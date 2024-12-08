<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TransactionController;

// ## PUBLIC ##

Route::post('/auth/login', [AuthController::class, "login"]);

Route::get('/boards', [BoardController::class, 'index']);

// Scoreboard
Route::get('/scoreboard/global/multiplayer', [GameController::class, 'globalMultiplayerStatistics']);
Route::get('/scoreboard/global/singleplayer', [GameController::class, 'indexGlobalScoreboard']);


// AUTHENTICATED
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/me', [UserController::class, 'me']);

    // Games
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::post('/games', [GameController::class, 'store']);

    // History 
    Route::get('/users/me/history', [GameController::class, 'history']);  
    
    // Scoreboard
    Route::get('/scoreboard/personal/singleplayer', [GameController::class, 'indexPersonalScoreboard']);
    Route::get('/scoreboard/personal/multiplayer', [GameController::class, 'personalMultiplayerStatistics']); 
});