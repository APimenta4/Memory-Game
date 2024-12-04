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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'type' => GameType::class,
        'status' => GameStatus::class,
        'began_at' => 'datetime',
        'ended_at' => 'datetime',
        'total_time' => 'decimal:2',
        'custom' => 'array',
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

    public function multiplayerGames()
    {
        return $this->hasMany(MultiplayerGamePlayed::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}