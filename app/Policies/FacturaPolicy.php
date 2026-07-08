<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FacturaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->is_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Factura $factura): bool
    {
        if (
            $user->is_admin
            && $user->cliente_id == $factura->propietario_id
            && in_array($factura->propietario_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->is_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Factura $factura): bool
    {
        if (
            $user->is_admin
            && $user->cliente_id ==  $factura->propietario_id
            && in_array($factura->propietario_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Factura $factura): bool
    {
        if (
            $user->is_admin
            && $user->cliente_id ==  $factura->propietario_id
            && in_array($factura->propietario_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Factura $factura): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Factura $factura): bool
    {
        return false;
    }

    public function cancel(User $user, Factura $factura): bool
    {
        if (
            $user->is_admin
            && $user->cliente_id ==  $factura->propietario_id
            && in_array($factura->propietario_id, $user->suscripciones_activas()->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    public function setPanelPac(User $user)
    {
        if ($user->is_admin)
            return true;

        return false;
    }
    public function setCabeceraFactura(User $user)
    {
        if ($user->is_admin)
            return true;

        return false;
    }


    //TODO Facturas del sistema
    /**
     * Determine whether the user can view any models.
     */
    public function viewAnyFacturaSistema(User $user): bool
    {
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewFacturaSistema(User $user, Factura $factura): bool
    {
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function createFacturaSistema(User $user): bool
    {
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateFacturaSistema(User $user, Factura $factura): bool
    {
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteFacturaSistema(User $user, Factura $factura): bool
    {
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restoreFacturaSistema(User $user, Factura $factura): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDeleteFacturaSistema(User $user, Factura $factura): bool
    {
        return false;
    }

    public function cancelFacturaSistema(User $user, Factura $factura): bool
    {
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    public function setPanelPacFacturaSistema(User $user)
    {
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }
    public function setCabeceraFacturaFacturaSistema(User $user)
    {
        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }
}
