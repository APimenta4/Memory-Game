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
        $perPage = $request->query('per_page', 10); // Get items per page from query, default to 10
        $multiplayerGames = $user->multiplayerGames->sortByDesc('began_at')->forPage($request->query('page', 1), $perPage);
    
        return response()->json([
            'data' => GameResource::collection($multiplayerGames),
            'current_page' => (int) $request->query('page', 1),
            'last_page' => ceil($user->multiplayerGames->count() / $perPage),
        ]);
    }
    
    public function singleplayerHistory(Request $request)
    {
        $user = $request->user();
        $perPage = $request->query('per_page', 10);
        $singleplayerGames = $user->singleplayerGames->sortByDesc('began_at')->forPage($request->query('page', 1), $perPage);
    
        return response()->json([
            'data' => GameResource::collection($singleplayerGames),
            'current_page' => (int) $request->query('page', 1),
            'last_page' => ceil($user->singleplayerGames->count() / $perPage),
        ]);
    }
    
    
}
