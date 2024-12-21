<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Game;
use App\Models\Transaction;
use App\Http\Requests\StatisticsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    private $today;
    private $startOfLastWeek;
    private $endOfLastWeek;
    private $oneWeekAgo;
    private $startOfLastMonth;
    private $endOfLastMonth;
    private $startOfThisYear;
    private $startOfLastYear;
    private $endOfLastYear;

    public function __construct()
    {
        $this->today = Carbon::now();
        $this->startOfLastWeek = $this->today->copy()->subWeek()->startOfWeek();
        $this->endOfLastWeek = $this->today->copy()->subWeek()->endOfWeek();
        $this->oneWeekAgo = $this->today->copy()->subWeek();
        $this->startOfLastMonth = $this->today->copy()->subMonth()->startOfMonth();
        $this->endOfLastMonth = $this->today->copy()->subMonth()->endOfMonth();
        $this->startOfThisYear = $this->today->copy()->startOfYear();
        $this->startOfLastYear = $this->today->copy()->subYear()->startOfYear();
        $this->endOfLastYear = $this->today->copy()->subYear()->endOfYear();
    }

    private function getGlobalStatistics()
    {
        $stats = [];

        $stats['All users total games completed'] = Game::where('status', 'E')->count();

        $stats['games_completed_last_7_days'] = Game::where('status', 'E')
            ->whereBetween('created_at', [$this->oneWeekAgo, $this->today])
            ->count();

        $stats['games_completed_last_month'] = Game::where('status', 'E')
            ->whereBetween('created_at', [$this->startOfLastMonth, $this->endOfLastMonth])
            ->count();

        $stats['games_completed_last_year'] = Game::where('status', 'E')
            ->whereBetween('created_at', [$this->startOfLastYear, $this->endOfLastYear])
            ->count();

        $stats['Total users registered'] = User::where('type', 'P')->count();

        return $stats;
    }

    public function indexGlobalStatistics(Request $request)
    {
        $stats = $this->getGlobalStatistics($request);
        return response()->json($stats);
    }

    public function indexPersonalStatistics(StatisticsRequest $request)
    {
        $user = $request->user();
        $globalStats = $this->getGlobalStatistics($request);

        $totalGamesByUser = Game::where('created_user_id', $user->id)->count();

        $calculatePercentage = function ($boardId) use ($user, $totalGamesByUser) {
            $gamesOnBoard = Game::where('created_user_id', $user->id)
                ->where('board_id', $boardId)
                ->where('status', 'E')
                ->count();

            return $totalGamesByUser > 0
                ? round(($gamesOnBoard / $totalGamesByUser) * 100, 2)
                : 0;
        };

        $stats = collect([
            "Total games played by {$user->nickname}" => $totalGamesByUser,
            "percentage_games_on_board_1" => $calculatePercentage(1),
            "percentage_games_on_board_2" => $calculatePercentage(2),
            "percentage_games_on_board_3" => $calculatePercentage(3),
        ]);

        $allStats = $stats->merge($globalStats);

        return response()->json($allStats);
    }

    public function indexAdminStatistics(StatisticsRequest $request)
    {
        $stats = $this->getGlobalStatistics($request);

        $stats['Total spent by all users (€)'] = Transaction::sum('euros');

        // Percentagem de jogos para cada board de TODOS OS USERS
        $boards = [1, 2, 3];
        $totalGames = Game::where('status', 'E')->count();

        foreach ($boards as $boardId) {
            $gamesOnBoard = Game::where('board_id', $boardId)
                ->where('status', 'E')
                ->count();

            $stats["percentage_games_on_board_{$boardId}"] = $totalGames > 0
                ? round(($gamesOnBoard / $totalGames) * 100, 2)
                : 0;
        }

        $stats['all_purchases_today'] = Transaction::where('type', 'P')
        ->whereDate('transaction_datetime', '=', $this->today->toDateString())
        ->sum('euros');

        $stats['all_purchases_last_7_days'] = Transaction::where('type', 'P')
            ->where('transaction_datetime', '>=', $this->oneWeekAgo)
            ->sum('euros');

        $stats['all_purchases_last_week'] = Transaction::where('type', 'P')
            ->where('transaction_datetime', [$this->startOfLastMonth, $this->endOfLastMonth])
            ->sum('euros');

        $stats['all_purchases_this_month'] = Transaction::where('type', 'P')
            ->where('transaction_datetime', '>=', $this->startOfLastMonth)
            ->sum('euros');

        $stats['all_purchases_last_month'] = Transaction::where('type', 'P')
            ->whereBetween('transaction_datetime', [$this->startOfLastMonth, $this->endOfLastMonth])
            ->sum('euros');

        $stats['all_purchases_this_year'] = Transaction::where('type', 'P')
            ->where('transaction_datetime', '>=', $this->startOfThisYear)
            ->sum('euros');

        $stats['all_purchases_last_year'] = Transaction::where('type', 'P')
            ->whereBetween('transaction_datetime', [$this->startOfLastYear, $this->endOfLastYear])
            ->sum('euros');

        return response()->json($stats);
    }
}
