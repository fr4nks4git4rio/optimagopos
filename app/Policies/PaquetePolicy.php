<?php

namespace App\Policies;

use App\Models\Paquete;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaquetePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('packages-viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Paquete $paquete): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('packages-view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('packages-create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Paquete $paquete): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('packages-update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Paquete $paquete): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('packages-delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Paquete $paquete): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('packages-restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Paquete $paquete): bool
    {
        return false;
    }
}
