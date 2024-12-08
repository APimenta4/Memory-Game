<?php

namespace App\Enums;

enum ScoreboardScope: string
{
    case PERSONAL = 'Personal';
    case GLOBAL = 'Global';

    public function label(): string
    {
        return match ($this) {
            ScoreboardScope::PERSONAL => 'Personal',
            ScoreboardScope::GLOBAL => 'Global',
        };
    }
}
