<?php

namespace App\Observers;

use App\Models\Factura;
use Illuminate\Support\Arr;

class FacturaObserver
{
    private $attr_except = [
        'id',
        'fecha_certificacion_str',
        'fecha_certificacion_en',
        'fecha_emision_str',
        'fecha_emision_en',
        'direccion_xml_relativa',
        'direccion_codigo_qr_relativa',
        'label_combo',
        'label',
        'value',
        'created_at',
        'updated_at'
    ];
    /**
     * Handle the factura "created" event.
     *
     * @param  Factura $factura
     * @return void
     */
    public function created(Factura $factura)
    {
        $label = 'Factura';
        activity("$label Creada")
            ->on($factura)
            ->event('created')
            ->withProperties(Factura::parseData(Arr::except(
                $factura->toArray(),
                $this->attr_except
            )))
            ->log("$label con ID: $factura->id ha sido creada.");
    }

    /**
     * Handle the factura "updated" event.
     *
     * @param Factura $factura
     * @return void
     */
    public function updated(Factura $factura)
    {
        $attributes = Arr::except(
            $factura->getDirty(),
            $this->attr_except
        );
        $label = 'Factura';
        activity("$label Actualizada")
            ->on($factura)
            ->event('updated')
            ->withProperty('attributes', Factura::parseData($attributes))
            ->withProperty('old', Factura::parseData(Arr::only($factura->getOriginal(), array_keys($attributes))))
            ->log("$label con ID: $factura->id ha sido actualizado.");
    }

    /**
     * Handle the factura "deleted" event.
     *
     * @param  Factura $factura
     * @return void
     */
    public function deleted(Factura $factura)
    {
        $label = 'Factura';
        activity("$label Desactivada")
            ->on($factura)
            ->event('deleted')
            ->withProperties(Factura::parseData(Arr::except(
                $factura->toArray(),
                $this->attr_except
            )))
            ->log("$label con ID: $factura->id ha sido eliminada.");
    }
}
