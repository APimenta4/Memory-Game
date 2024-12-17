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
use App\Notifications\TransactionNotification;
use Illuminate\Support\Collection;
use App\Models\Transaction;



class GameController extends Controller
{
    public function store(GameRequest $request)
    {
        $checkNotification = true;
        $brain_coins = 0;
        $user = $request->user();
        $newGame = new Game();
        $newGame->fill($request->validated());
        $newGame->created_user_id = $user->id;

        switch ($newGame->status) {
            case GameStatus::PENDING:
                if($newGame->type === GameType::MULTIPLAYER){
                    $this->checkPlayerBalance($user, -5);
                    $brain_coins = -5;
                } else{
                    $this->checkPlayerBalance($user, -1);
                    if(!($newGame->board->board_cols===3 
                      && $newGame->board->board_rows===4)){
                        $this->checkPlayerBalance($user, -1);
                        $brain_coins = -1;
                    }
                }
                break;
            case GameStatus::PLAYING:
                $newGame->began_at = now();
                if($newGame->type === GameType::SINGLEPLAYER){
                    if(!($newGame->board->board_cols===3 
                      && $newGame->board->board_rows===4)){
                        $this->checkPlayerBalance($user, -1);
                        $brain_coins = -1;
                    }
                }
                break;
            case GameStatus::ENDED:
                // For TAES APP
                $newGame->ended_at = now();
                $newGame->began_at = Carbon::parse($newGame->ended_at)->subSeconds($newGame->total_time);
                // notifications
                $checkNotification = true;
                break;
            default:
                throw ValidationException::withMessages([
                    "status.in" =>
                    'The status must be one of PE (Pending), PL (Playing), E (Ended).'
                ]);
        }
        $newGame->save();
        
        if ($checkNotification){
            // notifications
            $this->checkNewRecord($newGame, $user, 'total_time');
            $this->checkNewRecord($newGame, $user, 'total_turns_winner');
        }
        
        if($brain_coins !== 0){
            $this->internalTransaction($user, $newGame->id, $brain_coins);
        }
            
        // Save player 1 (for multiplayer games)
        if($newGame->type == GameType::MULTIPLAYER){
            $newGame->players()->attach($request->user()->id);
        }
        return new GameResource($newGame);
    }

    public function show(Game $game)
    {
        // not implemented
        //return new GameResource($game);
    }


    public function update(GameUpdateRequest $request, Game $game)
    {
        $checkNotification = false;
        $brain_coins = 0;
        $data = $request->validated();
        $user = $request->user();
        $newStatus = GameStatus::tryFrom($data["status"]);

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
            $game->began_at = now();
            $this->checkPlayerBalance($user, -5);
            $brain_coins = -5;       
        }
        else if ($game->status == GameStatus::PLAYING) {
            if ($newStatus == GameStatus::ENDED) {
                if($game->type == GameType::MULTIPLAYER){
                    // multiplayer
                    // check if player is in players of the game
                    if (!$data['winner_user_id']) {
                        throw ValidationException::withMessages([
                            "winner_user_id.in" =>
                            'A Ended Multiplayer game requires the winner id.'
                        ]);
                    }
                    $game->winner_user_id = $data['winner_user_id'];
                    $brain_coins = 7;
                }
                $game->ended_at = now();
                $game->total_time = $data["total_time"];
                $game->total_turns_winner = $data["total_turns_winner"];
                $checkNotification = true;
            }
        }

        $game->status = $newStatus;
        $game->save();

        if($brain_coins !== 0){
            if($game->type == GameType::MULTIPLAYER && $game->status == GameStatus::ENDED){
                $user = $game->winner;
            }

            $this->internalTransaction($user, $game->id, $brain_coins);
        }

        if($game->type == GameType::MULTIPLAYER && $game->status == GameStatus::PLAYING){
            $game->players()->attach($request->user()->id);
        }

        if ($checkNotification){
            // notifications
            $this->checkNewRecord($game, $user, 'total_time');
            $this->checkNewRecord($game, $user, 'total_turns_winner');
        }
        return new GameResource($game);
    }


    /**
     * Check if the user has sufficient balance to spend.
     *
     * @param  User  $user  The user whose balance will be checked.
     * @param  int  $brain_coins  The amount of brain coins to be spent.
     * @return void
     *
     * @throws ValidationException If the user does not have enough brain coins.
     */
    public function checkPlayerBalance(User $user, int $brain_coins){
        if ($user->brain_coins_balance - $brain_coins < 0){
            throw ValidationException::withMessages([
                "user.brain_coins_balance" => 'The User does not have enough brain coins.',
            ]);
        }
    }

    /**
     * Make internal Transactions.
     *
     * @param  User  $user
     * @param  int  $game_id
     * @param  int  $brain_coins
     * @return void
     */
    public function internalTransaction(User $user, int $game_id, int $brain_coins){
        $transaction = Transaction::create([
            'transaction_datetime' => now(),
            'user_id' => $user->id,
            'type' => 'I',
            'game_id' => $game_id,
            'brain_coins' => $brain_coins,
        ]);
        $transaction->save();

        $user->brain_coins_balance += $transaction->brain_coins;
        $user->save();

        if ($brain_coins > 0){
            $user->notify(new TransactionNotification($transaction));
        }
    }

    /**
     * Check if the game is a new record based on the provided top scores.
     *
     * @param  Game  $game
     * @param  User  $user
     * @param  string  $score_type ('total_time' or 'total_turns_winner')
     * @return void
     */
    public function checkNewRecord(Game $game, User $user, $score_type)
    {
        // Global
        $topScoresGlobal = $this->getTopScores($game, false, $score_type);
        $position = $this->findPositionInTopScores($game, $topScoresGlobal);
        if ($position !== null) {
            $user->notify(new TopScoreNotification(
                $game->id,
                $user,
                'global',
                $game->board->board_cols.'x'.$game->board->board_rows,
                $position,
                $score_type,
                $game->{$score_type}
            ));
        }
    
        // Personal
        $topScoresPersonal = $this->getTopScores($game, true, $score_type);
        $position = $this->findPositionInTopScores($game, $topScoresPersonal);
        if ($position !== null) {
            $user->notify(new TopScoreNotification(
                $game->id,
                $user,
                'personal',
                $game->board->board_cols.'x'.$game->board->board_rows,
                $position,
                $score_type,
                $game->{$score_type}
            ));
        }
    }
    
    /**
     * Retrieve the top scores based on the specified criteria.
     *
     * @param Game $game
     * @param bool $isPersonal
     * @param string $score_type ('total_time' or 'total_turns_winner')
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
     * @param Game $game
     * @param Collection $topScores
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
