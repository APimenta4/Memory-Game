<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Game;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Enums\GameType;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'nickname',
        'blocked',
        'photo_filename',
        'brain_coins_balance',
        'custom'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'custom' => 'array',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function singleplayerGames()
    {
        return $this->hasMany(Game::class, 'created_user_id')->where('type', GameType::SINGLEPLAYER);
    }

    public function multiplayerGames()
    {
        return $this->belongsToMany(Game::class, 'multiplayer_games_played', 'user_id', 'game_id')
            ->withPivot('player_won', 'pairs_discovered');
    }

    public function allGames()
    {
        return Game::where(function ($query) {
            $query->where('created_user_id', $this->id)
                ->orWhereHas('players', function ($query) {
                    $query->where('user_id', $this->id);
                });
        });
    }


}
