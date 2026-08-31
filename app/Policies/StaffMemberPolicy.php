<?php

namespace App\Policies;

use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StaffMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasGlobalRole('admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasGlobalRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->hasGlobalRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StaffMember $staffMember): bool
    {
        return $user->hasGlobalRole('admin');
    }
}
