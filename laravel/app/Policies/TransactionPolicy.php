<?php

namespace App\Policies;

use App\Models\User;

class TransactionPolicy
{
    public function create(User $user): bool
    {
        return $user->type != 'A';
    }
}
