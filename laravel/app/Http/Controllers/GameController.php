<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Enums\GameType;
use Illuminate\Http\Request;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\GameResourceNew;

class GameController extends Controller
{
    public function index()
    {
        //
    }

    public function store(GameRequest $request)
    {

        $newGame = new Game();
        $newGame->fill($request->validated());
        $newGame->save();
        return new GameResource($newGame);
    }

    public function show(Game $game)
    {
        return new GameResource($game);
    }

    public function update(Request $request, Game $game)
    {
        //
    }

    public function destroy(Game $game)
    {
        //
    }
    
}
