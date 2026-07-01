<?php

namespace App\Policies;

use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SuscripcionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if (user()->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Suscripcion $suscripcion): bool
    {
        if (user()->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (user()->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Suscripcion $suscripcion): bool
    {
        if (!in_array($suscripcion->estado, ['PENDIENTE', 'ACTIVA']))
            return false;

        if (user()->is_super_admin)
            return true;

        return false;
    }

    public function activate(User $user, Suscripcion $suscripcion): bool
    {
        if ($suscripcion->estado != 'PENDIENTE')
            return false;

        if (user()->is_super_admin)
            return true;

        return false;
    }

    public function revoke(User $user, Suscripcion $suscripcion): bool
    {
        if (!in_array($suscripcion->estado, ['PENDIENTE', 'ACTIVA']))
            return false;

        if (user()->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Suscripcion $suscripcion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Suscripcion $suscripcion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Suscripcion $suscripcion): bool
    {
        return false;
    }
}
