<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Resources\GameResource;
use App\Http\Requests\HistoryRequest;
use App\Http\Requests\ScoreboardRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        return new GameResource($game);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        //
    }

    public function multiplayerHistory(HistoryRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'began_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $boardId = $validated['board_id'] ?? null;
        $won = $validated['won'] ?? false;
        $query = $user->multiplayerGames()->orderBy($sortBy, $sortOrder);
    
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

        if ($won) {
            $query->where('winner_user_id', $user->id);
        }
    
        $multiplayerGames = $query->paginate($perPage);
    
        return GameResource::collection($multiplayerGames);
    }
    
    public function singleplayerHistory(HistoryRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'began_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $boardId = $validated['board_id'] ?? null;

        $query = $user->singleplayerGames()->orderBy($sortBy, $sortOrder);
    
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
    
        $singleplayerGames = $query->paginate($perPage);
    
        return GameResource::collection($singleplayerGames);
    }

    public function bothSingleplayerScoreboard(ScoreboardRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $scoreboardType = $validated['scoreboard_type'] === 'time' ? 'total_time' : 'total_turns_winner';
        $boardId = $validated['board_id'];
        $isOwnScoreboard = $validated['is_own_scoreboard'] ?? false;

        $query = Game::where('type', 'S')
            ->where('board_id', $boardId)
            ->where('status', 'E')
            ->orderBy($scoreboardType)
            ->orderBy('created_at', 'asc')
            ->limit(10);

        if ($isOwnScoreboard) {
            $query = $query->where('created_user_id', $user->id);
        }

        return GameResource::collection($query->get());
    }

    public function personalMultiplayerScoreboard(Request $request)
    {
        $user = $request->user();
        $victories = $user->multiplayerGames()
            ->where('winner_user_id', $user->id)
            ->count();
        $losses = $user->multiplayerGames()
            ->where('status','E')
            ->where('winner_user_id', '!=', $user->id)
            ->count();
        
        return [
            'victories' => $victories,
            'losses' => $losses,
        ];
    }

    public function globalMultiplayerScoreBoard(Request $request)
    {
        $topPlayers = Game::select('winner_user_id', DB::raw('count(*) as victories'))
            ->where('status', 'E')
            ->where('type', 'M')
            ->groupBy('winner_user_id')
            ->orderByDesc('victories')
            ->orderBy(DB::raw('max(ended_at)'), 'asc')
            ->limit(5)
            ->get()
            ->map(function ($victory) {
                $victory->nickname = User::find($victory->winner_user_id)->nickname;
                return [
                    'victories' => $victory->victories,
                    'nickname' => $victory->nickname,
                ];
            });
    
        return $topPlayers;
    }


}
