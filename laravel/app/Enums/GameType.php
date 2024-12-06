<?php

namespace App\Enums;

enum GameType: string
{
    case SINGLEPLAYER = 'S';
    case MULTIPLAYER = 'M';

    public function label(): string
    {
        return match ($this) {
            GameType::SINGLEPLAYER => 'Single-player',
            GameType::MULTIPLAYER => 'Multiplayer',
        };
    }
}
