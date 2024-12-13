<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use App\Enums\GameStatus;
use App\Enums\GameType;
use Illuminate\Support\Carbon;
use App\Http\Requests\GameRequest;
use App\Http\Resources\GameResource;
use App\Http\Requests\GameUpdateRequest;
use Illuminate\Validation\ValidationException;
use App\Notifications\TopScoreNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;



class GameController extends Controller
{
    public function index()
    {
        //
    }

    public function store(GameRequest $request)
    {
        // TODO dont allow singleplayer games have multiplayers status?
        $user = $request->user();
        $newGame = new Game();
        $newGame->fill($request->validated());
        $newGame->created_user_id = $user->id;

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
                // notifications
                $this->checkNewRecord($newGame, $user, 'total_time');
                $this->checkNewRecord($newGame, $user, 'total_turns_winner');
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
        $checkNotification = false;
        $data = $request->validated();
        $user = $request->user();

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
                
                $checkNotification = true;
            }
        }
        $game->status = $newStatus;
        $game->save();

        if ($checkNotification){
            // notifications
            $this->checkNewRecord($game, $user, 'total_time');
            $this->checkNewRecord($game, $user, 'total_turns_winner');
        }
        return new GameResource($game);
    }

    /**
     * Check if the game is a new record based on the provided top scores.
     *
     * @param  Game  $game        The game instance.
     * @param  User  $game        The game instance.
     * @param  string  $score_type The type of score being evaluated ('total_time' or 'total_turns_winner').
     * @return void
     */
    public function checkNewRecord(Game $game, User $user, $score_type)
    {
        // Fetch top scores for global scope
        $topScoresGlobal = $this->getTopScores($game, false, $score_type);
    
        // Check for global position
        $position = $this->findPositionInTopScores($game, $topScoresGlobal);
        if ($position !== null) {
            $user->notify(new TopScoreNotification(
                $game->id,
                $user,
                'global', // Scope
                $game->board->board_cols.'x'.$game->board->board_rows,
                $position,
                $score_type,
                $game->{$score_type}
            ));

            // TODO transaction of bonus credit
            return;
        }
    
        // Fetch top scores for personal scope
        $topScoresPersonal = $this->getTopScores($game, true, $score_type);
    
        // Check for personal position
        $position = $this->findPositionInTopScores($game, $topScoresPersonal);
        if ($position !== null) {
            $user->notify(new TopScoreNotification(
                $game->id,
                $user,
                'personal', // Scope
                $game->board->board_cols.'x'.$game->board->board_rows,
                $position,
                $score_type,
                $game->{$score_type}
            ));
            
            // TODO transaction of bonus credit
            return;
        }
    }
    
    /**
     * Retrieve the top scores based on the specified criteria.
     *
     * @param Game $game The game instance.
     * @param bool $isPersonal Whether the scores are personal or global.
     * @param string $score_type The type of score ('total_time' or 'total_turns_winner').
     * @return Collection The collection of top scores.
     */
    protected function getTopScores(Game $game, bool $isPersonal, string $score_type)
    {
        $query = Game::where('type', 'S');
        if ($isPersonal) {
            $query = $query->where('created_user_id', $game->created_user_id);
        }
        // Execute the query and return the top 3 scores
        return $query->where('board_id', $game->board->id)
                     ->where('status', 'E')
                     ->orderBy($score_type, 'asc')
                     ->orderBy('ended_at', 'asc')
                     ->limit(3)
                     ->get();
    }
    
    /**
     * Find the position of the game in the given top scores.
     *
     * @param Game $game The game instance.
     * @param Collection $topScores The collection of top scores.
     * @return int|null The 1-based position if the game is found, otherwise null.
     */
    protected function findPositionInTopScores(Game $game, Collection $topScores)
    {
        foreach ($topScores as $index => $currentGame) {
            if ($currentGame->id === $game->id) {
                return $index + 1; 
            }
        }
        return null;
    }

}
