<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users-viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if ($user->can('users-view') && $model->cliente_id == $user->cliente_id)
                return true;
            return false;
        }

        return $user->can('users-view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('users-create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if ($user->can('users-update') && $model->cliente_id == $user->cliente_id)
                return true;
            return false;
        }

        return $user->can('users-update');
    }

    public function assignPermissions(User $user, User $model)
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if ($user->can('users-assignPermissions') && $model->cliente_id == $user->cliente_id && !$model->hasRole('Admin'))
                return true;
            return false;
        }

        return $user->can('users-assignPermissions');
    }
    public function setBranches(User $user, User $model)
    {
        if ($model->hasAnyRole(['SuperAdmin', 'Accountant']))
            return false;

        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if ($user->can('users-setBranches') && $model->cliente_id == $user->cliente_id && !$model->hasRole('Admin'))
                return true;
            return false;
        }

        return $user->can('users-setBranches');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if ($user->can('users-delete') && $model->cliente_id == $user->cliente_id)
                return true;
            return false;
        }

        return $user->can('users-delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        if ($user->hasAnyRole(['Admin', 'Manager'])) {
            if ($user->can('users-restore') && $model->cliente_id == $user->cliente_id)
                return true;
            return false;
        }

        return $user->can('users-restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
