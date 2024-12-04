<?php

namespace App\Enums;

enum GameType: string
{
    case SIMPLE = 'S';
    case MEDIUM = 'M';
    case HARD = 'H';

    public function label(): string
    {
        return match ($this) {
            GameType::SIMPLE => 'Simple',
            GameType::MEDIUM => 'Medium',
            GameType::HARD => 'Hard',
        };
    }
}
