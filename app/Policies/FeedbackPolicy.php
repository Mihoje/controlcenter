<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAll(User $user){
        return $user->isAdmin();
    }

    public function view(User $user, User $forUser){
        return $user->isAdmin() || $user->is($forUser);
    }

    public function create(User $user){
        return true;
    }

    public function acknowledge(User $user){
        return $user->isAdmin();
    }

    public function publish(User $user){
        return $user->isAdmin();
    }
}
