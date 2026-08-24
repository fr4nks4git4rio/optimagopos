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
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('clients-viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewCliente(User $user, Cliente $cliente): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('clients-view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function createCliente(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('clients-create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateCliente(User $user, Cliente $cliente): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            return false;
        }

        return $user->can('clients-update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteCliente(User $user, Cliente $cliente): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('clients-delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restoreCliente(User $user, Cliente $cliente): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('clients-restore');
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
        if ($user->hasAnyRole('SuperAdmin', 'Admin'))
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewComensal(User $user, Cliente $cliente): bool
    {
        if ($user->hasRole('SuperAdmin'))
            return true;

        if ($user->hasRole('Admin') && in_array($cliente->id, $user->cliente->comensales->pluck('id')->toArray()))
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function createComensal(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateComensal(User $user, Cliente $cliente): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function deleteComensal(User $user, Cliente $cliente): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restoreComensal(User $user, Cliente $cliente): bool
    {
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
