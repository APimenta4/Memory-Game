<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\BoardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/users/me', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [AuthController::class, "login"]);

// AFONSO
// Route::get('/games/{game}', [GameController::class, 'show']); nao foi preciso ate agora
Route::get('/boards', [BoardController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
Route::get('/users/me/history/singleplayer', [GameController::class, 'singleplayerHistory']);
Route::get('/users/me/history/multiplayer', [GameController::class, 'multiplayerHistory']);
Route::get('/scoreboard/myScoreboard', [GameController::class, 'singleplayerScoreboard']);
})->middleware('auth:sanctum');