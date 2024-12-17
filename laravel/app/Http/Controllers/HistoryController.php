<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\GameResource;
use App\Http\Resources\GameDetailedResource;
use App\Http\Requests\HistoryRequest;
use App\Models\Game;

class HistoryController extends Controller
{
    public function index(HistoryRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $type = $validated['type'] ?? null;
        $perPage = $validated['per_page'] ?? null;
        $status = $validated['status'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'began_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $boardId = $validated['board_id'] ?? null;
        $won = $validated['won'] ?? false;
    
        if ($user->type === 'A') {
            if ($type === 'multiplayer') {
                $query = Game::where('type', 'M')->orderBy($sortBy, $sortOrder);
            } elseif ($type === 'singleplayer') {
                $query = Game::where('type', 'S')->orderBy($sortBy, $sortOrder);
            } else {
                $query = Game::orderBy($sortBy, $sortOrder);
            }
        } else {
            if ($type === 'multiplayer') {
                $query = $user->multiplayerGames()->orderBy($sortBy, $sortOrder);
            } elseif ($type === 'singleplayer') {
                $query = $user->singleplayerGames()->orderBy($sortBy, $sortOrder);
            } else {
                $query = $user->allGames()->orderBy($sortBy, $sortOrder);
            }
        }
    
        if ($status) {
            $query->where('status', $status);
        }
    
        if ($startDate) {
            $query->whereDate('began_at', '>=', $startDate);
        }
    
        if ($endDate) {
            $query->whereDate('began_at', '<=', $endDate);
        }
    
        if ($boardId) {
            $query->where('board_id', $boardId);
        }
    
        if ($type === 'multiplayer' && $won && $user->type !== 'A') {
            $query->where('winner_user_id', $user->id);
        }
    
        $games = $query->paginate($perPage);
        
        return GameResource::collection($games);
    }
    
}
