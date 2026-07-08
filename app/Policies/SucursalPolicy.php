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
        if ($user->cliente_id && $user->is_admin)
            return true;

        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sucursal $sucursal): bool
    {
        if ($user->cliente_id) {
            if (
                $user->is_admin
                && in_array($sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
                && in_array($sucursal->id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->cliente_id)
            return false;

        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sucursal $sucursal): bool
    {
        if ($user->cliente_id) {
            if (
                $user->is_admin
                && in_array($sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
                && in_array($sucursal->id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sucursal $sucursal): bool
    {
        if ($user->cliente_id) {
            if (
                $user->is_admin
                && in_array($sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
                && in_array($sucursal->id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sucursal $sucursal): bool
    {
        if ($user->cliente_id) {
            if (
                $user->is_admin
                && in_array($sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
                && in_array($sucursal->id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
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
        if ($user->cliente_id) {
            if (
                $user->is_admin
                && in_array($sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
                && in_array($sucursal->id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }
    public function setPaymentForms(User $user, Sucursal $sucursal): bool
    {
        if ($user->cliente_id) {
            if (
                $user->is_admin
                && in_array($sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
                && in_array($sucursal->id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }
}
