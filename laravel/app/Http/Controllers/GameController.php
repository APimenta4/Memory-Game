<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Enums\GameStatus;
use App\Enums\GameType;
use Illuminate\Support\Carbon;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Http\Requests\GameUpdateRequest;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    public function index()
    {
        //
    }

    public function store(GameRequest $request)
    {
        // TODO dont allow singleplayer games have multiplayers status?
        $newGame = new Game();
        $newGame->fill($request->validated());
        $newGame->created_user_id = $request->user()->id;

        switch ($newGame->status) {
            case GameStatus::PENDING:
                break;  
            case GameStatus::PLAYING:
                $newGame->began_at = now();
                break;
            case GameStatus::ENDED:
                // For TAES APP
                $newGame->ended_at = now();
                $newGame->began_at = Carbon::parse($newGame->ended_at)->subSeconds($newGame->total_time);
                break;
            default:
                throw ValidationException::withMessages([
                    "status.in" =>
                        'The status must be one of PE (Pending), PL (Playing), E (Ended).'
                ]);
                break;
        }

        $newGame->save();
        return new GameResource($newGame);
    }

    public function show(Game $game)
    {
        return new GameResource($game);
    }

    public function update(GameUpdateRequest $request, Game $game)
    {
        $data = $request->validated();

        $newStatus = GameStatus::tryFrom($data["status"]);
        if (!$newStatus) {
            throw ValidationException::withMessages([
                "status.in" =>
                    'The status must be one of PE (Pending), PL (Playing), E (Ended) or I (Interrupted).'
            ]);
        }

        if ($game->status == GameStatus::ENDED || $game->status == GameStatus::INTERRUPTED){
            throw ValidationException::withMessages([
                "status" =>
                    "Cannot change game #" .
                    $game->id .
                    " status from '" .
                    $game->status->value .
                    "' to '$newStatus->value'!",
            ]);
        }
        else if ($game->status == GameStatus::PENDING){
            // multiplayer
        }
        else if ($game->status == GameStatus::PLAYING) {
            if ($newStatus == GameStatus::ENDED) {
                $game->ended_at = now();
                
                if($game->type == GameType::MULTIPLAYER){
                    // multiplayer
                    // check if player is in players of the game
                    $winner_user_id =  $data['winner_user_id'];
                    if (!$winner_user_id) {
                        throw ValidationException::withMessages([
                            "winner_user_id.in" =>
                                'A Ended Multiplayer game requires the winner id.'
                        ]);
                    }
                    $game->winner_user_id = $winner_user_id;
                }
                $game->total_time = $data["total_time"];
                $game->total_turns_winner = $data["total_turns_winner"];
            }
        }
        $game->status = $newStatus;
        $game->save();
        return new GameResource($game);
    }

    public function destroy(Game $game)
    {    
        if ($game->created_user_id !== auth()->user()->id) {
            return response()->json(['error' => 'You are not authorized to delete this game.'], 403);
        }
        $game->delete();
        return response()->json(null, 204);
    }
}
