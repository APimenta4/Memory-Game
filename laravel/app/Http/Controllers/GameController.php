<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Resources\GameResource;
use App\Http\Requests\HistoryRequest;
use App\Http\Requests\ScoreboardRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Enums\ScoreboardScope;

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


    public function history(HistoryRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $type = $validated['type'] ?? null;
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status'] ?? null;
        $sortBy = $validated['sort_by'] ?? 'began_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $boardId = $validated['board_id'] ?? null;
        $won = $validated['won'] ?? false;

        if ($type === 'multiplayer') {
            $query = $user->multiplayerGames()->orderBy($sortBy, $sortOrder);
        } elseif($type === 'singleplayer') {
            $query = $user->singleplayerGames()->orderBy($sortBy, $sortOrder);
        } else {
            $query = $user->allGames()->orderBy($sortBy, $sortOrder);
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

        if ($type === 'multiplayer' && $won) {
            $query->where('winner_user_id', $user->id);
        }

        $games = $query->paginate($perPage);

        return GameResource::collection($games);
    }

    // -----------------------------------------
    // Scoreboard    

    // -----------------------------------------
    // PERSONAL and GLOBAL multiplayer statistics

    public function personalMultiplayerStatistics(Request $request)
    {
        $user = $request->user();
        $victories = $user->multiplayerGames()
            ->where('winner_user_id', $user->id)
            ->count();
        $losses = $user->multiplayerGames()
            ->where('status', 'E')
            ->where('winner_user_id', '!=', $user->id)
            ->count();
        $win_percentage = $victories + $losses > 0 ? $victories / ($victories + $losses) : 0;
        $win_percentage = number_format($win_percentage * 100, 0);

        return [
            'victories' => $victories,
            'losses' => $losses,
            'win_percentage' => $win_percentage,
        ];
    }

    public function globalMultiplayerStatistics(Request $request)
    {
        // top 5 players with the most multiplayer victories across any board
        $topPlayers = Game::select('winner_user_id', DB::raw('count(*) as victories'))
            ->where('status', 'E')
            ->where('type', 'M')
            ->groupBy('winner_user_id')
            ->orderByDesc('victories')
            ->orderBy(DB::raw('max(ended_at)'), 'asc')
            ->limit(5)
            ->get()
            ->map(function ($victory, $index) {
                $victory->nickname = User::find($victory->winner_user_id)->nickname;
                return [
                    'position' => $index + 1,
                    'victories' => $victory->victories,
                    'nickname' => $victory->nickname,
                ];
            });

        // if the request is made by a logged in user, calculate and append his personal position
        if ($request->user('sanctum')) {
            $user = $request->user('sanctum');
            $userVictories = Game::where('winner_user_id', $user->id)
                ->where('status', 'E')
                ->where('type', 'M')
                ->count();
            $userPosition = Game::select('winner_user_id', DB::raw('count(*) as victories'))
                ->where('status', 'E')
                ->where('type', 'M')
                ->groupBy('winner_user_id')
                ->orderByDesc('victories')
                ->orderBy(DB::raw('max(ended_at)'), 'asc')
                ->get()
                ->search(function ($victory) use ($user) {
                    return $victory->winner_user_id === $user->id;
                });

            $userPosition = $userPosition === false ? 'N/A' : $userPosition + 1;

            // if the user is already in the top 5, we don't need to append his position
            if ($userPosition <= 5) {
                return $topPlayers;
            }

            $userScoreboard = [
                'position' => $userPosition,
                'victories' => $userVictories,
                'nickname' => $user->nickname,
            ];

            $topPlayers->push($userScoreboard);
        }

        return $topPlayers;
    }


    // -----------------------------------------
    // PERSONAL and GLOBAL singleplayer scoreboards

    public function indexPersonalScoreboard(ScoreboardRequest $request)
    {
        $games = $this->singleplayerScoreboard($request, ScoreboardScope::PERSONAL);
        return GameResource::collection($games);
    }

    public function indexGlobalScoreboard(ScoreboardRequest $request)
    {
        $games = $this->singleplayerScoreboard($request, ScoreboardScope::GLOBAL );
        return GameResource::collection($games);
    }

    public function singleplayerScoreboard(ScoreboardRequest $request, ScoreboardScope $scope)
    {
        $user = $request->user();
        $validated = $request->validated();
        $scoreboardType = $validated['scoreboard_type'] === 'time' ? 'total_time' : 'total_turns_winner';
        $boardId = $validated['board_id'];

        $query = Game::where('type', 'S')
            ->where('board_id', $boardId)
            ->where('status', 'E')
            ->orderBy($scoreboardType)
            ->orderBy('created_at', 'asc')
            ->limit(5);

        if ($scope === ScoreboardScope::PERSONAL) {
            $query = $query->where('created_user_id', $user->id);
        }

        return GameResource::collection($query->get());
    }

}
