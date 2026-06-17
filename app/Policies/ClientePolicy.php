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
        if ($user->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewCliente(User $user, Cliente $cliente): bool
    {
        if ($user->is_super_admin)
            return true;

        if ($user->is_admin &&  $cliente->id == $user->cliente_id)
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function createClente(User $user): bool
    {
        if ($user->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateCliente(User $user, Cliente $cliente): bool
    {
        if ($user->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteCliente(User $user, Cliente $cliente): bool
    {
        if ($user->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restoreCliente(User $user, Cliente $cliente): bool
    {
        if ($user->is_super_admin)
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
        if ($user->is_admin && in_array($cliente->id, $user->cliente->comensales()->get()->pluck('id')))
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
    public function update(User $user, Cliente $cliente): bool
    {
        if ($user->is_admin && in_array($cliente->id, $user->cliente->comensales()->get()->pluck('id')))
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        if ($user->is_admin && in_array($cliente->id, $user->cliente->comensales()->get()->pluck('id')))
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cliente $cliente): bool
    {
        if ($user->is_admin && in_array($cliente->id, $user->cliente->comensales()->get()->pluck('id')))
            return true;

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return false;
    }

    //TODO -----FIN COMENSAL-----
}
