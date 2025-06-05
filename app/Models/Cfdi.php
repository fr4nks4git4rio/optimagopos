<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Cfdi
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string codigo
 * @property string descripcion
 * @property boolean activo
 */
class Cfdi extends Model
{
    use LogsActivity;

    public $table = 'tb_cfdis';

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
                    'created' => 'El CFDI ha sido creado.',
                    'updated' => 'El CFDI ha sido actualizado.',
                    'deleted' => 'El CFDI ha sido eliminado.',
                    'restored' => 'El CFDI ha sido restaurado.',
                    'forceDeleted' => 'El CFDI ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('CFDI')
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
            'codigo' => ['required', Rule::unique('tb_cfdis')->ignore($this->id)],
            'descripcion' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Campo requerido.',
            'codigo.unique' => 'El código ya existe.',
            'descripcion.required' => 'Campo requerido'
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
}
