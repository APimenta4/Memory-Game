<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Resources\GameResource;
use App\Http\Requests\HistoryRequest;
use App\Http\Requests\ScoreboardRequest;

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

    public function singleplayerScoreboard(ScoreboardRequest $request)
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
            $ownMultiplayerStats = $user->multiplayerGames()
                ->where('status', 'E')
                ->get()
                ->reduce(function ($carry, $game) use ($user) {
                    $carry['total_victories'] += $game->winner_user_id === $user->id ? 1 : 0;
                    $carry['total_losses'] += $game->winner_user_id !== $user->id ? 1 : 0;
                    return $carry;
                }, ['total_victories' => 0, 'total_losses' => 0]);
            return [
                'data' => GameResource::collection($query->get()),
                'multiplayerStats' => $ownMultiplayerStats,
            ];
        }

        $globalMultiplayerStats = Game::where('type', 'M')
            ->where('status', 'E')
            ->get()
            ->reduce(function ($carry, $game) {
            if (!isset($carry[$game->winner_user_id])) {
                $carry[$game->winner_user_id] = [
                'user' => $game->winner,
                'total_victories' => 0,
                'last_win' => $game->ended_at,
                ];
            }
            $carry[$game->winner_user_id]['total_victories'] += 1;
            $carry[$game->winner_user_id]['last_win'] = $game->ended_at;
            return $carry;
            }, []);

        usort($globalMultiplayerStats, function ($a, $b) {
            if ($b['total_victories'] === $a['total_victories']) {
                return $a['last_win'] <=> $b['last_win'];
            }
            return $b['total_victories'] <=> $a['total_victories'];
        });

        $globalMultiplayerStats = array_slice($globalMultiplayerStats, 0, 5);


        return [
            'data' => GameResource::collection($query->get()),
            'multiplayerStats' => $globalMultiplayerStats,
        ];
    }


}
