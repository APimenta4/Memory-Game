<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Game;
use App\Models\Transaction;
use App\Http\Requests\StatisticsRequest;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function indexGlobalStatistics(Request $request)
    {
        $user = $request->user();
    
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $stats = [];

        //generic statistics
  
        $stats['all users total games completed'] = Game::where('status', 'completed')->count();


        //last week

        //last month

        //last year

        $stats['total_users_registed'] = User::where('type','P')
        ->count();

      

        return response()->json($stats);
    }

 
    public function indexPersonalStatistics(StatisticsRequest $request)
    {
            //agora para um user em especifcio

            $user = $request->user();
            $stats = [];
    
            $stats['total_games_played_by_user'] = Game::where('created_user_id', $user->id)->count();
                
            $stats['most_active'] = User::withCount('allGames')
                ->orderBy('all_games_count', 'desc')
                ->limit(1)
                ->first();

            return response()->json($stats);
    }

    public function indexAdminStatistics(StatisticsRequest $request)
    {
        $user = $request->user();
        $stats = [];

        $stats['total_spent'] = Transaction::sum('euros');

        return response()->json($stats);
    }
}
