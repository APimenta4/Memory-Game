<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Game;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        return $this->hasMany(Game::class, 'created_user_id');
    }
    
    public function multiplayerGames()
    {
        return $this->belongsToMany(Game::class, 'multiplayer_games_played', 'user_id', 'game_id')
            ->withPivot('player_won', 'pairs_discovered');
    }

    public function allGames()
    {
        $singlePlayerGames = $this->singlePlayerGames()->get();
        $multiplayerGames = $this->multiplayerGames()->get();
    
        return $singlePlayerGames->merge($multiplayerGames);
    }
    

}
