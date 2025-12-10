<?php

namespace App\Policies;

use anlutro\LaravelSettings\Facade as Setting;
use App\Helpers\TrainingStatus;
use App\Models\Area;
use App\Models\TrainingBan;
use App\Models\Training;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

use Carbon\Carbon;

class TrainingBanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view training bans.
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->isModeratorOrAbove();
    }

    public function create(User $user){
        return $user->isModeratorOrAbove();
    }

    public function revoke(User $user, TrainingBan $ban){
        return $user->isAdmin() || ($user == $ban->issuer && $user->isModeratorOrAbove());
    }
}
