<?php

namespace App\Observers;

use App\Models\Paquete;
use App\Models\Suscripcion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PaqueteObserver
{
    private $attr_except = ['id', 'value', 'label', 'created_at', 'updated_at', 'deleted_at'];
    /**
     * Handle the Paquete "created" event.
     */
    public function created(Paquete $paquete): void
    {
        activity("Paquete Creado")
            ->on($paquete)
            ->event('created')
            ->withProperties(Arr::except(
                $paquete->toArray(),
                $this->attr_except
            ))
            ->log("Paquete: $paquete->nombre ha sido creado.");
    }

    /**
     * Handle the Paquete "updated" event.
     */
    public function updated(Paquete $paquete): void
    {
        $attributes = Arr::except(
            $paquete->getDirty(),
            $this->attr_except
        );

        if (key_exists('precio', $attributes)) {
            if ($attributes['precio'] < $paquete->getOriginal('precio')) {
                Suscripcion::where('paquete_id', $paquete->id)->lazy()->each(function (Suscripcion $suscripcion) use ($attributes) {
                    $suscripcion->precio_paquete = $attributes['precio'];
                    $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                    $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;
                    $suscripcion->save();
                });
            } else {
                Suscripcion::where('paquete_id', $paquete->id)->whereHas('cliente', function ($query) {
                    $query->where('es_cliente_fiel', 0);
                })->lazy()->each(function (Suscripcion $suscripcion) use ($attributes) {
                    $suscripcion->precio_paquete = $attributes['precio'];
                    $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                    $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;
                    $suscripcion->save();
                });
            }
        }

        if (key_exists('cant_sucursales', $attributes)) {
            if ($attributes['cant_sucursales'] > $paquete->getOriginal('cant_sucursales')) {
                Suscripcion::where('paquete_id', $paquete->id)->lazy()->each(function (Suscripcion $suscripcion) use ($attributes) {
                    $suscripcion->cant_sucursales = $attributes['cant_sucursales'];
                    $suscripcion->save();
                });
            }
        }

        if (key_exists('cant_terminales', $attributes)) {
            if ($attributes['cant_terminales'] > $paquete->getOriginal('cant_terminales')) {
                Suscripcion::where('paquete_id', $paquete->id)->lazy()->each(function (Suscripcion $suscripcion) use ($attributes) {
                    $suscripcion->cant_terminales = $attributes['cant_terminales'];
                    $suscripcion->save();
                });
            }
        }

        if (key_exists('cant_usuarios', $attributes)) {
            if ($attributes['cant_usuarios'] > $paquete->getOriginal('cant_usuarios')) {
                Suscripcion::where('paquete_id', $paquete->id)->lazy()->each(function (Suscripcion $suscripcion) use ($attributes) {
                    $suscripcion->cant_usuarios = $attributes['cant_usuarios'];
                    $suscripcion->save();
                });
            }
        }

        if (key_exists('cant_timbres', $attributes)) {
            if ($attributes['cant_timbres'] > $paquete->getOriginal('cant_timbres')) {
                Suscripcion::where('paquete_id', $paquete->id)->whereHas('modulos',  function ($query) {
                    $query->where('id',  3);
                })->lazy()->each(function (Suscripcion $suscripcion) use ($attributes) {
                    $suscripcion->cant_timbres = $attributes['cant_timbres'];
                    $suscripcion->save();
                });
            }
        }

        if (key_exists('cant_meses_analitica_basica', $attributes)) {
            if ($attributes['cant_meses_analitica_basica'] > $paquete->getOriginal('cant_meses_analitica_basica')) {
                Suscripcion::where('paquete_id', $paquete->id)->whereHas('modulos',  function ($query) {
                    $query->where('id',  4);
                })->lazy()->each(function (Suscripcion $suscripcion) use ($attributes) {
                    $suscripcion->cant_meses_analitica_basica = $attributes['cant_meses_analitica_basica'];
                    $suscripcion->save();
                });
            }
        }

        if (count($attributes) > 0) {
            activity("Paquete Actualizado")
                ->on($paquete)
                ->event('updated')
                ->withProperty('attributes', $attributes)
                ->withProperty('old', Arr::only($paquete->getOriginal(), array_keys($attributes)))
                ->log("Paquete $paquete->nombre ha sido actualizado.");
        }
    }

    /**
     * Handle the Paquete "deleted" event.
     */
    public function deleted(Paquete $paquete): void
    {
        activity("Paquete Desactivado")
            ->on($paquete)
            ->event('deleted')
            ->withProperties(Arr::except(
                $paquete->toArray(),
                $this->attr_except
            ))
            ->log("Paquete $paquete->nombre ha sido desactivado.");
    }

    /**
     * Handle the Paquete "restored" event.
     */
    public function restored(Paquete $paquete): void
    {
        activity("Paquete Restaurado")
            ->on($paquete)
            ->event('restored')
            ->withProperties(Arr::except(
                $paquete->toArray(),
                $this->attr_except
            ))
            ->log("Paquete $paquete->nombre ha sido restaurado.");
    }

    /**
     * Handle the Paquete "force deleted" event.
     */
    public function forceDeleted(Paquete $paquete): void
    {
        activity("Paquete Eliminado Permanentemente")
            ->on($paquete)
            ->withProperties(Arr::except(
                $paquete->toArray(),
                $this->attr_except
            ))
            ->log("Paquete $paquete->nombre ha sido eliminado permanentemente.");
    }
}
