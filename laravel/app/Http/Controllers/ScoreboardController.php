<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Enums\ScoreboardScope;
use App\Http\Resources\GameResource;
use App\Http\Requests\ScoreboardRequest;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ScoreboardController extends Controller
{
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

    public function indexPersonalScoreboard(ScoreboardRequest $request)
    {
        return $this->singleplayerScoreboard($request, ScoreboardScope::PERSONAL);
    }

    public function indexGlobalScoreboard(ScoreboardRequest $request)
    {
        return $this->singleplayerScoreboard($request, ScoreboardScope::GLOBAL);
    }

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

    // Statistics
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
}
