<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Enums\GameType;
use Illuminate\Http\Request;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Http\Requests\HistoryRequest;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function queryGame(HistoryRequest $request, GameType $type)
    {
        $validated = $request->validated();
        $user = $request->user();
        $perPage = $validated['per_page'] ?? 10;
        $status = $validated['status']?? null;
        $sortBy = $validated['sort_by'] ?? 'began_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $startDate = $validated['start_date']?? null;
        $endDate = $validated['end_date']?? null;
        $boardId = $validated['board_id']?? null;
        $won = $validated['won']?? null;


        if ($type === GameType::MULTIPLAYER){
            $query = $user->multiplayerGames()->orderBy($sortBy, $sortOrder);
            if ($won) {
                $query->where('winner_user_id', $user->id);
            }
        }else{
            $query = $user->singleplayerGames()->orderBy($sortBy, $sortOrder);
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
        return $query->paginate($perPage);
    }


    public function indexSinglePlayer(HistoryRequest $request)
    {
        $games = $this->queryGame($request,GameType::SINGLEPLAYER);
        return GameResource::collection($games);
    }

    public function indexMultiPlayer(HistoryRequest $request)
    {
        $games = $this->queryGame($request,GameType::MULTIPLAYER);
        return GameResource::collection($games);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GameRequest $request)
    {

        $newGame = new Game();
        $newGame->fill($request->validated());
        $newGame->save();
        return new GameResource($newGame);
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
}
