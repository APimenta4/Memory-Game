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
use App\Http\Controllers\NotificationController;

use App\Models\Game;
use App\Models\Transaction;


// PUBLIC

Route::post('/auth/login', [AuthController::class, "login"]);

Route::get('/boards', [BoardController::class, 'index']);

// Scoreboard
Route::get('/scoreboards/global/multiplayer', [ScoreboardController::class, 'globalMultiplayerStatistics']);
Route::get('/scoreboards/global/singleplayer', [ScoreboardController::class, 'indexGlobalScoreboard']);

Route::get('/test/{game}', [GameController::class, 'getTopScoresTest']);

// AUTHENTICATED
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/me', [UserController::class, 'me']);

    // Games
    Route::post('/games', [GameController::class, 'store'])->can('create', Game::class);
    Route::put('/games/{game}', [GameController::class, 'update'])->can('update', 'game');

    // History 
    Route::get('/history', [HistoryController::class, 'index']);
    
    // Scoreboard
    Route::get('/scoreboards/personal/singleplayer', [ScoreboardController::class, 'indexPersonalScoreboard']);
    Route::get('/scoreboards/personal/multiplayer', [ScoreboardController::class, 'personalMultiplayerStatistics']);

    // Transactions
    Route::post('/transactions/buy-coins', [TransactionController::class, 'buyBrainCoins'])->can('create', Transaction::class);
    Route::get('/transactions/history', [TransactionController::class, 'transactionHistory']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});