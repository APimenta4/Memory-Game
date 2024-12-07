<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Resources\GameResource;

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

    public function multiplayerHistory(Request $request)
    {
        $user = $request->user();
        $perPage = $request->query('per_page', 10);
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'began_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $boardId = $request->query('board_id');
        $won = $request->query('won', false);
    
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
    
        return response()->json([
            'data' => GameResource::collection($multiplayerGames),
            'current_page' => $multiplayerGames->currentPage(),
            'last_page' => $multiplayerGames->lastPage(),
        ]);
    }
    
    public function singleplayerHistory(Request $request)
    {
        $user = $request->user();
        $perPage = $request->query('per_page', 10);
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'began_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $boardId = $request->query('board_id');

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
    
        return response()->json([
            'data' => GameResource::collection($singleplayerGames),
            'current_page' => $singleplayerGames->currentPage(),
            'last_page' => $singleplayerGames->lastPage(),
        ]);
    }


}
