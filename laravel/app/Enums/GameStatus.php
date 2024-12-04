<?php

namespace App\Enums;

enum GameStatus: string
{
    case PENDING = 'PE';
    case PLAYING = 'PL';
    case ENDED = 'E';
    case INTERRUPTED = 'I';

    public function label(): string
    {
        return match ($this) {
            GameStatus::PENDING => 'Pending',
            GameStatus::PLAYING => 'Playing',
            GameStatus::ENDED => 'Ended',
            GameStatus::INTERRUPTED => 'Interrupted',
        };
    }
}
