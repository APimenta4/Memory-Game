<?php

namespace App\Models;

use App\Models\User;
use App\Enums\GameType;
use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $fillable = [
        'created_user_id',
        'winner_user_id',
        'type',
        'status',
        'began_at',
        'ended_at',
        'total_time',
        'total_turns_winner',
        'board_id',
        'custom',
    ];

    protected $casts = [
        //'type' => GameType::class,
        'status' => GameStatus::class,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // "players" users association through multiplayer_games_played 
    public function players()
    {
        return $this->belongsToMany(User::class, 'multiplayer_games_played', 'game_id', 'user_id')->withPivot('player_won', 'pairs_discovered');
    }

    
}