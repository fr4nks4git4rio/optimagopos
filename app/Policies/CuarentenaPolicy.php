<?php

namespace App\Policies;

use App\Models\Cuarentena;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CuarentenaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('quarantine-viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cuarentena $cuarentena): bool
    {
        return $user->can('quarantine-viewAny');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cuarentena $cuarentena): bool
    {
        return false;
    }

    public function fix(User $user, Cuarentena $cuarentena): bool
    {
        return $user->can('quarantine-fix');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cuarentena $cuarentena): bool
    {
        return $user->can('quarantine-delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cuarentena $cuarentena): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cuarentena $cuarentena): bool
    {
        return false;
    }
}
