<?php

namespace App\Policies;

use App\Models\FileMain;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileMainPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create files.
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasGlobalRole('admin');
    }

    /**
     * Determine whether the user can update the file.
     *
     * @return bool
     */
    public function update(User $user, FileMain $file)
    {
        return $user->hasGlobalRole('admin');
    }

    /**
     * Determine whether the user can delete the file.
     *
     * @return bool
     */
    public function delete(User $user, FileMain $file)
    {
        return $user->hasGlobalRole('admin');
    }
}
