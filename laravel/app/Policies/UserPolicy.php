<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manage(User $user): bool
    {
        return $user->type == 'A';
    }

    public function delete(User $user): bool
    {
        return $user->type == 'A' || $user->id = auth()->id();
    }


}
