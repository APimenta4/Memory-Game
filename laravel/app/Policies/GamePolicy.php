<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Game;
use App\Enums\GameType;

class GamePolicy
{
    public function update(User $user, Game $game): bool
    {
        // administrators can't update games
        if ($user->type == 'A') {
            return false;
        }
        // if its singleplayer, only the creator can update it
        // if its multiplayer, anyone can join (and update) it
        return $game->type == GameType::SINGLEPLAYER ? $user->id == $game->created_user_id : true;
    }

    public function create(User $user): bool
    {
        // administrators can't create or start games
        return $user->type != 'A';
    }


}
