<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class FormaPago
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:47 pm CST
 *
 * @property string codigo
 * @property string descripcion
 * @property boolean activo
 */
class FormaPago extends Model
{
    use LogsActivity;

    public $table = 'tb_forma_pagos';

    protected $appends = ['nombre', 'value', 'label', 'text'];

    public $fillable = [
        'codigo',
        'descripcion',
        'activo'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'codigo' => 'string',
        'descripcion' => 'string'
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
                    'created' => 'La Forma de Pago ha sido creada.',
                    'updated' => 'La Forma de Pago ha sido actualizada.',
                    'deleted' => 'La Forma de Pago ha sido eliminada.',
                    'restored' => 'La Forma de Pago ha sido restaurada.',
                    'forceDeleted' => 'La Forma de Pago ha sido eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Forma de Pago')
            ->logExcept(['created_at', 'updated_at'])
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
            'codigo' => ['required', Rule::unique('tb_forma_pagos')->ignore($this->id)],
            'descripcion' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Campo requerido.',
            'codigo.unique' => 'La código ya existe.',
            'descripcion.required' => 'Campo requerido.'
        ];
    }
    public function getNombreAttribute()
    {
        return $this->descripcion . ' (' . $this->codigo . ')';
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function getLabelAttribute()
    {
        return $this->nombre;
    }

    public function getTextAttribute()
    {
        return $this->nombre;
    }

    public static function findByCode($code){
        return self::where('codigo', $code)->where('activo', 1)->first();
    }
}
