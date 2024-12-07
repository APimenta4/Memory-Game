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
        $multiplayerGames = $user->multiplayerGames->sortByDesc('began_at');
        return response()->json(GameResource::collection($multiplayerGames));
    }
    
    public function singleplayerHistory(Request $request)
    {
        $user = $request->user();
        $singleplayerGames = $user->singleplayerGames->sortByDesc('began_at');
        return response()->json(GameResource::collection($singleplayerGames));
    }
    
    
}
