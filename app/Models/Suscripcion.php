<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Summary of Suscripcion
 *
 * @property integer $cant_sucursales
 * @property integer $cant_terminales
 * @property integer $cant_usuario
 * @property date $fecha_inicio_operaciones
 * @property date $fecha_inicio_pagos
 * @property string $periodicidad_pagos
 * @property float $precio_paquete
 * @property float $precio_extra
 * @property float $precio_total
 * @property float $descuento
 * @property float $total
 * @property string $estado
 * @property integer $cliente_id
 * @property integer $paquete_id
 */
class Suscripcion extends Model
{
    use HasFactory;

    public $table = 'tb_suscripciones';

    public $fillable = [
        'cant_sucursales',
        'cant_terminales',
        'cant_usuarios',
        'fecha_inicio_operaciones',
        'fecha_inicio_pagos',
        'periodicidad_pagos',
        'precio_paquete',
        'precio_extra',
        'precio_total',
        'descuento',
        'total',
        'estado',
        'cliente_id',
        'paquete_id'
    ];

    protected  $appends = ['value', 'label', 'pendiente_facturar', 'multiplicador_periodicidad'];

    protected $casts = [
        'fecha_inicio_operaciones' => 'date',
        'fecha_inicio_pagos' => 'date',
        'created_at' => 'datetime'
    ];

    public function getValueAttribute()
    {
        return $this->getKey();
    }

    public function getLabelAttribute()
    {
        return "Suscripción #{$this->getKey()} - " . ($this->paquete()->exists() ? $this->paquete->nombre : 'Custom');
    }

    public function getPendienteFacturarAttribute()
    {
        return $this->total - $this->factura_conceptos()->sum(DB::raw('cantidad * precio_unitario'));
    }

    public function getMultiplicadorPeriodicidadAttribute()
    {
        return match ($this->periodicidad_pagos) {
            'MENSUAL' => 1,
            'BIMESTRAL' => 2,
            'TRIMESTRAL' => 3,
            'SEMESTRAL' => 6,
            'ANUAL' => 12,
            default => 1
        };
    }

    public function generarPdf()
    {
        $subtotal = $this->precio_paquete + $this->precio_extra;

        if ($subtotal <= 0) {
            $porciento_descuento = 0;
        } else {
            $porciento_descuento = ($this->descuento / $subtotal) * 100;
        }

        $nombre = $this->cliente->nombre_comercial;
        // En tu componente de Livewire o en un controlador dedicado:
        $pdf = Pdf::loadView('pdf.confirmacion-suscripcion', [
            'id' => $this->id,
            'cliente_id' => $this->cliente->id,
            'created_at' => $this->created_at,
            'cliente' => Crypt::decrypt($nombre),
            'rfc' => $this->cliente->rfc,
            'es_cliente_fiel' => $this->cliente->es_cliente_fiel,
            'estado' => $this->estado,
            'paquete' => $this->paquete,
            'precio_paquete' => $this->precio_paquete,
            'precio_extra' => $this->precio_extra,
            'precio_total' => $this->precio_total,
            'descuento' => $this->descuento,
            'porcentaje_descuento' => $porciento_descuento,
            'moneda' => system_config('moneda_sistema'),
            'paquete_id' => $this->paquete_id,
            'cant_sucursales' => $this->cant_sucursales,
            'cant_terminales' => $this->cant_terminales,
            'cant_usuarios' => $this->cant_usuarios,
            'fecha_inicio_operaciones' => $this->fecha_inicio_operaciones,
            'fecha_inicio_pagos' => $this->fecha_inicio_pagos,
            'periodicidad_pagos' => $this->periodicidad_pagos,
            'modulos' => $this->modulos,
            'modulos_paquete' => $this->paquete ? $this->paquete->modulos->pluck('id')->toArray() : []
            // ... todas tus variables del formulario
        ]);

        $name = 'Confirmacion_Suscripcion_' . $this->cliente->rfc . '.pdf';
        $pdf->save($name);
        return $name;
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    public function paquete()
    {
        return $this->belongsTo(Paquete::class);
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'tb_suscripciones_modulos', 'suscripcion_id', 'modulo_id');
    }

    public function factura_conceptos()
    {
        return $this->hasMany(FacturaConcepto::class);
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'suscripcion_id');
    }
    public function terminales()
    {
        return $this->hasMany(Terminal::class, 'suscripcion_id');
    }
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'tb_suscripciones_usuarios', 'suscripcion_id', 'usuario_id');
    }
}
