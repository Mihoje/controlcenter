<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeedbackPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAll(User $user){
        return $user->hasGlobalRole('admin');
    }

    public function view(User $user, User $forUser){
        return $user->hasGlobalRole('admin') || $user->is($forUser);
    }

    public function create(User $user){
        return true;
    }

    public function acknowledge(User $user){
        return $user->hasGlobalRole('admin');
    }

    public function publish(User $user){
        return $user->hasGlobalRole('admin');
    }

    /**
     * Determine whether the user can update feedback in general.
     */
    public function update(User $user, ?Feedback $feedback = null): bool
    {
        if ($feedback === null) {
            return $user->hasPermission('feedback.update');
        }

        if ($feedback->referencePosition) {
            return $user->hasPermission('feedback.update', $feedback->referencePosition->area);
        }

        return $user->hasPermission('feedback.update')
            && $user->accessibleAreasForPermission('feedback.uncorrelated.view')->hasAccess();
    }
}
