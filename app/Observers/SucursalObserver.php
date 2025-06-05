<?php

namespace App\Observers;

use App\Models\Administracion\Traza;
use App\Models\Sucursal;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SucursalObserver
{
    private $attr_except = ['id', 'value', 'label', 'direccion_plain', 'logo_uri', 'codigo_postal', 'created_at', 'updated_at', 'deleted_at'];
    /**
     * Handle the sucursal "created" event.
     *
     * @param  Sucursal $sucursal
     * @return void
     */
    public function created(Sucursal $sucursal)
    {
        activity("Sucursal Creada")
            ->on($sucursal)
            ->event('created')
            ->withProperties(Sucursal::parseData(Arr::except(
                $sucursal->toArray(),
                $this->attr_except
            )))
            ->log("Sucursal con RFC: $sucursal->rfc ha sido creada.");
    }

    /**
     * Handle the sucursal "updated" event.
     *
     * @param Sucursal $sucursal
     * @return void
     */
    public function updated(Sucursal $sucursal)
    {
        $attributes = Arr::except(
            $sucursal->getDirty(),
            $this->attr_except
        );
        if(count($attributes) > 0){
            activity("Sucursal Actualizada")
            ->on($sucursal)
            ->event('updated')
            ->withProperty('attributes', Sucursal::parseData($attributes))
            ->withProperty('old', Sucursal::parseData(Arr::only($sucursal->getOriginal(), array_keys($attributes))))
            ->log("Sucursal con RFC: $sucursal->rfc ha sido actualizada.");
        }
    }

    /**
     * Handle the sucursal "deleted" event.
     *
     * @param  Sucursal $sucursal
     * @return void
     */
    public function deleted(Sucursal $sucursal)
    {
        activity("Sucursal Desactivada")
            ->on($sucursal)
            ->event('deleted')
            ->withProperties(Sucursal::parseData(Arr::except(
                $sucursal->toArray(),
                $this->attr_except
            )))
            ->log("Sucursal con RFC: $sucursal->rfc ha sido desactivada.");
    }

    /**
     * Handle the sucursal "restored" event.
     *
     * @param  Sucursal $sucursal
     * @return void
     */
    public function restored(Sucursal $sucursal)
    {
        activity("Sucursal Restaurado")
            ->on($sucursal)
            ->event('restored')
            ->withProperties(Sucursal::parseData(Arr::except(
                $sucursal->toArray(),
                $this->attr_except
            )))
            ->log("Sucursal con RFC: $sucursal->rfc ha sido restaurada.");
    }

    /**
     * Handle the sucursal "force deleted" event.
     *
     * @param  Sucursal $sucursal
     * @return void
     */
    public function forceDeleted(Sucursal $sucursal)
    {
        activity("Sucursal Eliminada Permanentemente")
            ->on($sucursal)
            ->withProperties(Sucursal::parseData(Arr::except(
                $sucursal->toArray(),
                $this->attr_except
            )))
            ->log("Sucursal con RFC: $sucursal->rfc ha sido eliminada permanentemente.");
    }
}
