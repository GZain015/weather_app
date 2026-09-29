<?php

namespace App\Policies;

use App\Models\Favourite;
use App\Models\User;

class FavouritePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user, Favourite $favourite): bool
    {
        return $user->id === $favourite->user_id;
    }
}
