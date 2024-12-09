<?php

namespace App\Enums;

enum TransactionType: string
{
    case BONUS = 'B';
    case PURCHASE = 'P';
    case INTERNAL = 'I';
}
