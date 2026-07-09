<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{
    //TODO -----INICIO CLIENTE-----
    /**
     * Determine whether the user can view any models.
     */
    public function viewAnyCliente(User $user): bool
    {
        if ($user->cliente_id)
            return false;

        if ($user->is_super_admin ||  $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewCliente(User $user, Cliente $cliente): bool
    {
        if ($user->is_admin &&  $cliente->id == $user->cliente_id)
            return true;

        if ($user->is_super_admin || $user->is_contabilidad)
            return true;


        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function createCliente(User $user): bool
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
    public function updateCliente(User $user, Cliente $cliente): bool
    {
        if ($user->cliente_id)
            return false;

        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    public function manageClientSuscripcion(User $user, Cliente $cliente): bool
    {
        if ($user->cliente_id)
            return false;

        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteCliente(User $user, Cliente $cliente): bool
    {
        if ($user->cliente_id)
            return false;

        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restoreCliente(User $user, Cliente $cliente): bool
    {
        if ($user->cliente_id)
            return false;

        if ($user->is_super_admin || $user->is_contabilidad)
            return true;

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDeleteCliente(User $user, Cliente $cliente): bool
    {
        return false;
    }

    //TODO -----FIN CLIENTE-----


    //TODO  -----INICIO COMENSAL-----
    /**
     * Determine whether the user can view any models.
     */
    public function viewAnyComensal(User $user): bool
    {
        if ($user->is_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewComensal(User $user, Cliente $cliente): bool
    {
        if ($user->is_admin && in_array($cliente->id, $user->cliente->comensales->pluck('id')->toArray()))
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function createComensal(User $user): bool
    {
        if ($user->is_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateComensal(User $user, Cliente $cliente): bool
    {
        if ($cliente->rfc === 'XAXX010101000')
            return false;

        if ($user->is_admin && in_array($cliente->id, $user->cliente->comensales_activos->pluck('id')->toArray()))
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteComensal(User $user, Cliente $cliente): bool
    {
        if ($user->is_admin && in_array($cliente->id, $user->cliente->comensales_activos->pluck('id')->toArray()))
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restoreComensal(User $user, Cliente $cliente): bool
    {
        if ($user->is_admin && in_array($cliente->id, $user->cliente->comensales_inactivos->pluck('id')->toArray()))
            return true;

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDeleteComensal(User $user, Cliente $cliente): bool
    {
        return false;
    }

    //TODO -----FIN COMENSAL-----
}
