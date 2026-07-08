<?php

namespace App\Observers;

use App\Models\Modulo;
use App\Models\Suscripcion;
use Illuminate\Support\Arr;

class ModuloObserver
{
    private $attr_except = ['id', 'value', 'label', 'created_at', 'updated_at', 'deleted_at'];
    /**
     * Handle the Modulo "created" event.
     */
    public function created(Modulo $modulo): void
    {
        activity("Módulo Creado")
            ->on($modulo)
            ->event('created')
            ->withProperties(Arr::except(
                $modulo->toArray(),
                $this->attr_except
            ))
            ->log("Módulo: $modulo->nombre ha sido creado.");
    }

    /**
     * Handle the Modulo "updated" event.
     */
    public function updated(Modulo $modulo): void
    {
        $attributes = Arr::except(
            $modulo->getDirty(),
            $this->attr_except
        );

        if (key_exists('costo_base', $attributes)) {
            if ($attributes['costo_base'] < $modulo->getOriginal('costo_base')) {
                $modulo->suscripciones()
                    ->with('paquete.modulos')
                    ->lazy()
                    ->each(function (Suscripcion $suscripcion) use ($modulo, $attributes) {

                        // RESTRICCIÓN ÓPTIMA: Usamos la colección en memoria ($suscripcion->paquete->modulos)
                        // en lugar de consultar a la base de datos con modulos()
                        if (!$suscripcion->paquete->modulos->contains('id', $modulo->id)) {

                            $diferenciaCosto = $modulo->getOriginal('costo_base') - $attributes['costo_base'];

                            $suscripcion->precio_paquete -= $diferenciaCosto;
                            $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                            $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;

                            $suscripcion->save(); // Esto disparará los Observers de Suscripción correctamente
                        }
                    });
            } else {
                $modulo->suscripciones()
                    ->whereHas('cliente', function ($query) {
                        $query->where('es_cliente_fiel', 0);
                    })
                    ->with('paquete.modulos')
                    ->lazy()
                    ->each(function (Suscripcion $suscripcion) use ($modulo, $attributes) {

                        // RESTRICCIÓN ÓPTIMA: Usamos la colección en memoria ($suscripcion->paquete->modulos)
                        // en lugar de consultar a la base de datos con modulos()
                        if (!$suscripcion->paquete->modulos->contains('id', $modulo->id)) {

                            $diferenciaCosto = $attributes['costo_base'] - $modulo->getOriginal('costo_base');

                            $suscripcion->precio_paquete += $diferenciaCosto;
                            $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                            $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;

                            $suscripcion->save(); // Esto disparará los Observers de Suscripción correctamente
                        }
                    });
            }
        }

        if (count($attributes) > 0) {
            activity("Módulo Actualizado")
                ->on($modulo)
                ->event('updated')
                ->withProperty('attributes', $attributes)
                ->withProperty('old', Arr::only($modulo->getOriginal(), array_keys($attributes)))
                ->log("Módulo $modulo->nombre ha sido actualizado.");
        }
    }

    /**
     * Handle the Modulo "deleted" event.
     */
    public function deleted(Modulo $modulo): void
    {
        activity("Módulo Desactivado")
            ->on($modulo)
            ->event('deleted')
            ->withProperties(Arr::except(
                $modulo->toArray(),
                $this->attr_except
            ))
            ->log("Módulo $modulo->nombre ha sido desactivado.");
    }

    /**
     * Handle the Modulo "restored" event.
     */
    public function restored(Modulo $modulo): void
    {
        activity("Módulo Restaurado")
            ->on($modulo)
            ->event('restored')
            ->withProperties(Arr::except(
                $modulo->toArray(),
                $this->attr_except
            ))
            ->log("Módulo $modulo->nombre ha sido restaurado.");
    }

    /**
     * Handle the Modulo "force deleted" event.
     */
    public function forceDeleted(Modulo $modulo): void
    {
        activity("Módulo Eliminado Permanentemente")
            ->on($modulo)
            ->withProperties(Arr::except(
                $modulo->toArray(),
                $this->attr_except
            ))
            ->log("Módulo $modulo->nombre ha sido eliminado permanentemente.");
    }
}
