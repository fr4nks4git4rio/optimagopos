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
        return $user->can('terminals-viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Terminal $terminal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('terminals-view')
                && $terminal->sucursal->cliente_id == $user->cliente_id
                && in_array($terminal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('terminals-view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager']))
            return false;

        return $user->can('terminals-create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Terminal $terminal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('terminals-update')
                && $terminal->sucursal->cliente_id == $user->cliente_id
                && in_array($terminal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('terminals-update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Terminal $terminal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('terminals-delete')
                && $terminal->sucursal->cliente_id == $user->cliente_id
                && in_array($terminal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('terminals-delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Terminal $terminal): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if (
                $user->can('terminals-restore')
                && $terminal->sucursal->cliente_id == $user->cliente_id
                && in_array($terminal->suscripcion_id, $user->suscripciones_activas->pluck('id')->toArray())
            )
                return true;
            return false;
        }

        return $user->can('terminals-restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Terminal $terminal): bool
    {
        return false;
    }
}
