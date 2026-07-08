<?php

namespace App\Policies;

use App\Models\Terminal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TerminalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->is_super_admin)
            return true;

        if ($user->is_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Terminal $terminal): bool
    {
        if ($user->is_super_admin)
            return true;

        if (
            $user->is_admin
            && in_array($terminal->sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
            && in_array($terminal->id, $user->terminales->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->is_super_admin)
            return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Terminal $terminal): bool
    {
        if ($user->is_super_admin)
            return true;

        if (
            $user->is_admin
            && in_array($terminal->sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
            && in_array($terminal->id, $user->terminales->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Terminal $terminal): bool
    {
        if ($user->is_super_admin)
            return true;

        if (
            $user->is_admin
            && in_array($terminal->sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
            && in_array($terminal->id, $user->terminales->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Terminal $terminal): bool
    {
        if ($user->is_super_admin)
            return true;

        if (
            $user->is_admin
            && in_array($terminal->sucursal->cliente_id, $user->cliente->sucursales->pluck('cliente_id')->toArray())
            && in_array($terminal->id, $user->terminales->pluck('id')->toArray())
        )
            return true;

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Terminal $terminal): bool
    {
        return false;
    }
}
