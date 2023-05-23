<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_team');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->can('view_team');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_team');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): Response
    {
        return $user->can('edit_team') && $user->id === $team->created_by
            ? Response::allow()
            : Response::deny('You do not own this team.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): Response
    {
        return $user->can('delete_team') && $user->id === $team->created_by
            ? Response::allow()
            : Response::deny();
    }
}
