<?php

namespace App\Observers;

use App\Models\Administracion\Traza;
use App\Models\Cliente;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClienteObserver
{
    private $attr_except = ['id', 'value', 'label', 'direccion_plain', 'logo_uri', 'codigo_postal', 'created_at', 'updated_at', 'deleted_at'];
    /**
     * Handle the cliente "created" event.
     *
     * @param  Cliente $cliente
     * @return void
     */
    public function created(Cliente $cliente)
    {
        $label = $cliente->es_comensal ? 'Comensal' : 'Cliente';
        activity("$label Creado")
            ->on($cliente)
            ->event('created')
            ->withProperties(Cliente::parseData(Arr::except(
                $cliente->toArray(),
                $this->attr_except
            )))
            ->log("$label con RFC: $cliente->rfc ha sido creado.");
    }

    /**
     * Handle the cliente "updated" event.
     *
     * @param Cliente $cliente
     * @return void
     */
    public function updated(Cliente $cliente)
    {
        $attributes = Arr::except(
            $cliente->getDirty(),
            $this->attr_except
        );
        $label = $cliente->es_comensal ? 'Comensal' : 'Cliente';
        activity("$label Actualizado")
            ->on($cliente)
            ->event('updated')
            ->withProperty('attributes', Cliente::parseData($attributes))
            ->withProperty('old', Cliente::parseData(Arr::only($cliente->getOriginal(), array_keys($attributes))))
            ->log("$label con RFC: $cliente->rfc ha sido actualizado.");
    }

    /**
     * Handle the cliente "deleted" event.
     *
     * @param  Cliente $cliente
     * @return void
     */
    public function deleted(Cliente $cliente)
    {
        $label = $cliente->es_comensal ? 'Comensal' : 'Cliente';
        activity("$label Desactivado")
            ->on($cliente)
            ->event('deleted')
            ->withProperties(Cliente::parseData(Arr::except(
                $cliente->toArray(),
                $this->attr_except
            )))
            ->log("$label con RFC: $cliente->rfc ha sido desactivado.");
    }

    /**
     * Handle the cliente "restored" event.
     *
     * @param  Cliente $cliente
     * @return void
     */
    public function restored(Cliente $cliente)
    {
        $label = $cliente->es_comensal ? 'Comensal' : 'Cliente';
        activity("$label Restaurado")
            ->on($cliente)
            ->event('restored')
            ->withProperties(Cliente::parseData(Arr::except(
                $cliente->toArray(),
                $this->attr_except
            )))
            ->log("$label con RFC: $cliente->rfc ha sido restaurado.");
    }

    /**
     * Handle the cliente "force deleted" event.
     *
     * @param  Cliente $cliente
     * @return void
     */
    public function forceDeleted(Cliente $cliente)
    {
        $label = $cliente->es_comensal ? 'Comensal' : 'Cliente';
        activity("$label Eliminado Permanentemente")
            ->on($cliente)
            ->withProperties(Cliente::parseData(Arr::except(
                $cliente->toArray(),
                $this->attr_except
            )))
            ->log("$label con RFC: $cliente->rfc ha sido eliminado permanentemente.");
    }
}
