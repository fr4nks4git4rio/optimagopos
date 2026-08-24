<?php

namespace App\Policies;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SucursalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('branches-viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sucursal $sucursal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('branches-view')
                && $sucursal->cliente_id == $user->cliente_id
                && in_array($sucursal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('branches-view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('branches-create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sucursal $sucursal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('branches-update')
                && $sucursal->cliente_id == $user->cliente_id
                && in_array($sucursal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('branches-update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sucursal $sucursal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('branches-delete')
                && $sucursal->cliente_id == $user->cliente_id
                && in_array($sucursal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('branches-delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sucursal $sucursal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('branches-restore')
                && $sucursal->cliente_id == $user->cliente_id
                && in_array($sucursal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('branches-restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sucursal $sucursal): bool
    {
        return false;
    }

    public function setConfigs(User $user, Sucursal $sucursal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('branches-setConfigs')
                && $sucursal->cliente_id == $user->cliente_id
                && in_array($sucursal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('branches-setConfigs');
    }
    public function setPaymentForms(User $user, Sucursal $sucursal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('branches-setPaymentForms')
                && $sucursal->cliente_id == $user->cliente_id
                && in_array($sucursal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('branches-setPaymentForms');
    }
}
