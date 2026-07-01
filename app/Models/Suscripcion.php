<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        'estado',
        'cliente_id',
        'paquete_id'
    ];

    protected  $appends = ['pendiente_facturar', 'multiplicador_periodicidad'];

    protected $casts = [
        'fecha_inicio_operaciones' => 'date',
        'fecha_inicio_pagos' => 'date'
    ];

    public function getPendienteFacturarAttribute()
    {
        return $this->precio_total - $this->factura_conceptos()->sum(DB::raw('cantidad * precio_unitario'));
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
        return $this->belongsToMany(Modulo::class, 'tb_suscripcion_modulos', 'suscripcion_id', 'modulo_id');
    }

    public function factura_conceptos()
    {
        return $this->hasMany(FacturaConcepto::class);
    }
}
