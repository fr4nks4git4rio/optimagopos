<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class SucursalFormaPago
 * @package App\Models
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string $nombre
 * @property string $moneda_id
 * @property integer $forma_pago_id
 * @property integer $sucursal_id
 */
class SucursalFormaPago extends Model
{
    use LogsActivity, SoftDeletes;

    public $table = 'tb_sucursal_forma_pagos';

    public $fillable = [
        'nombre',
        'moneda_id',
        'forma_pago_id',
        'sucursal_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nombre' => 'string',
        'moneda_id' => 'integer',
        'form_pago_id' => 'integer',
        'sucursal_id' => 'integer'
    ];

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => 'La Forma de Pago de la Sucursal ha sido creada.',
                    'updated' => 'La Forma de Pago de la Sucursal ha sido actualizada.',
                    'deleted' => 'La Forma de Pago de la Sucursal ha sido desactivada.',
                    'restored' => 'La Forma de Pago de la Sucursal ha sido restaurada.',
                    'forceDeleted' => 'La Forma de Pago de la Sucursal ha sido eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Forma de Pago de Sucursal')
            ->logExcept(['created_at', 'updated_at', 'deleted_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public function rules()
    {
        return [
            'nombre' => ['required', Rule::unique('tb_sucursal_form_pagos')->ignore($this->id)],
            'moneda_id' => ['required', 'exists:tb_monedas,id'],
            'form_pago_id' => ['required', 'exists:tb_form_pagos,id'],
            'sucursal_id' => ['required', 'exists:tb_sucursales,id']
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'Campo requerido.',
            'codigo.unique' => 'El nombre ya está en uso.',
            'moneda.required' => 'Campo requerido.',
            'moneda_id.exists' => 'Moneda no encontrada.',
            'form_pago_id.required' => 'Campo requerido.',
            'form_pago_id.exists' => 'Forma de Pago no encontrada.',
            'sucursal_id.required' => 'Campo requerido.',
            'sucursal_id.exists' => 'Sucursal no encontrada.',
        ];
    }

    public function moneda()
    {
        return $this->belongsTo(Moneda::class);
    }
    public function forma_pago()
    {
        return $this->belongsTo(FormaPago::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
