<?php

namespace App\Observers;

use App\Models\Administracion\Traza;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserObserver
{
    private $attr_except = ['id', 'nombre_completo', 'password', 'created_at', 'updated_at', 'deleted_at'];
    /**
     * Handle the user "created" event.
     *
     * @param  User $user
     * @return void
     */
    public function created(User $user)
    {
        activity("Usuario Creado")
            ->on($user)
            ->event('created')
            ->withProperties(User::parseData(Arr::except(
                $user->toArray(),
                $this->attr_except
            )))
            ->log('Usuario: ' . $user->email . ' ha sido creado.');
    }

    /**
     * Handle the user "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        $attributes = Arr::except(
            $user->getDirty(),
            $this->attr_except
        );
        activity('Usuario Actualizado')
            ->on($user)
            ->event('updated')
            ->withProperty('attributes', User::parseData($attributes))
            ->withProperty('old', User::parseData(Arr::only($user->getOriginal(), array_keys($attributes))))
            ->log('Usuario: ' . $user->email . ' ha sido actualizado.');
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  User $user
     * @return void
     */
    public function deleted(User $user)
    {
        activity('Usuario Desactivado')
            ->on($user)
            ->event('deleted')
            ->withProperties(User::parseData(Arr::except(
                $user->toArray(),
                $this->attr_except
            )))
            ->log('Usuario: ' . $user->email . ' ha sido desactivado.');
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  User $user
     * @return void
     */
    public function restored(User $user)
    {
        activity('Usuario Restaurado')
            ->on($user)
            ->event('restored')
            ->withProperties(User::parseData(Arr::except(
                $user->toArray(),
                $this->attr_except
            )))
            ->log('Usuario: ' . $user->email . ' ha sido restaurado.');
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        activity('Usuario Eliminado Permanentemente')
            ->on($user)
            ->withProperties(User::parseData(Arr::except(
                $user->toArray(),
                $this->attr_except
            )))
            ->log('Usuario: ' . $user->email . ' ha sido eliminado permanentemente.');
    }
}
