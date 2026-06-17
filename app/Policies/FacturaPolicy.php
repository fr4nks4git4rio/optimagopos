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
        if ($user->is_admin && $user->cliente_id ==  $factura->propietario_id)
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
        if ($user->is_admin && $user->cliente_id ==  $factura->propietario_id)
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Factura $factura): bool
    {
        if ($user->is_admin && $user->cliente_id ==  $factura->propietario_id)
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

    public function viewPanelPac(User $user)
    {
        if ($user->is_admin)
            return true;

        return false;
    }
    public function viewCabeceraFactura(User $user)
    {
        if ($user->is_admin)
            return true;

        return false;
    }
}
