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
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;

use App\Models\Game;
use App\Models\Transaction;
use App\Models\User;


// PUBLIC

// User
Route::post('/register', [UserController::class, 'store']);
Route::post('/auth/login', [AuthController::class, "login"]);

Route::get('/boards', [BoardController::class, 'index']);

// Scoreboard
Route::get('/scoreboards/global/multiplayer', [ScoreboardController::class, 'globalMultiplayerStatistics']);
Route::get('/scoreboards/global/singleplayer', [ScoreboardController::class, 'indexGlobalScoreboard']);

Route::get('/test/{game}', [GameController::class, 'getTopScoresTest']);


//Statistics anonymous
Route::get('/statistics', [StatisticsController::class, 'indexGlobalStatistics']);

// AUTHENTICATED
Route::middleware('auth:sanctum')->group(function () {

    // Login
    Route::post('/auth/logout', [AuthController::class, 'logout']); 
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']); 
    
    Route::get('/users/me', [UserController::class, 'me']);
    Route::get('/users/balance', [UserController::class, 'showBalance']); // Fetch user profile

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']); // Fetch user profile
    Route::put('/profile', [ProfileController::class, 'update']); // Update profile
    Route::post('/profile', [ProfileController::class, 'changePhoto']); //change profile photo


    // Games
    Route::post('/games', [GameController::class, 'store'])->can('create', Game::class);
    Route::post('/games', [GameController::class, 'store']);
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::patch('/games/{game}', [GameController::class, 'update'])->can('update', 'game');

    // History 
    Route::get('/history', [HistoryController::class, 'index']);
    
    // Scoreboard
    Route::get('/scoreboards/personal/singleplayer', [ScoreboardController::class, 'indexPersonalScoreboard']);
    Route::get('/scoreboards/personal/multiplayer', [ScoreboardController::class, 'personalMultiplayerStatistics']);

    // Transactions
    Route::post('/transactions/buy-coins', [TransactionController::class, 'buyBrainCoins'])->can('create', Transaction::class);
    Route::post('/transactions', [TransactionController::class, 'store'])->can('create', Transaction::class);
    Route::get('/transactions/history', [TransactionController::class, 'transactionHistory']);

    //Statistics personal
    Route::get('/statistics/personal', [StatisticsController::class, 'indexPersonalStatistics']);

    //Statistics Admin
    Route::get('/statistics/admin', [StatisticsController::class, 'indexAdminStatistics']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Administrate users
    Route::get('/users', [UserController::class, 'index'])->can('manage', User::class);;
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::patch('/users/{user}/block', [UserController::class, 'block'])->can('manage', User::class);
    Route::patch('/users/{user}/unblock', [UserController::class, 'unblock'])->can('manage', User::class);
});