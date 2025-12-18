<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Mes
 * @package App\Models
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string $clave
 * @property string $descripcion
 */
class Mes extends Model
{
    use LogsActivity;

    public $table = 'tb_meses';

    protected $appends = ['nombre', 'value', 'label', 'text'];

    public $fillable = [
        'clave',
        'descripcion'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'clave' => 'string',
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
                    'created' => 'El Mes ha sido creada.',
                    'updated' => 'El Mes ha sido actualizada.',
                    'deleted' => 'El Mes ha sido eliminada.',
                    'restored' => 'El Mes ha sido restaurada.',
                    'forceDeleted' => 'El Mes ha sido eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Mes')
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
            'clave' => ['required', Rule::unique('tb_meses')->ignore($this->id)],
            'descripcion' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'clave.required' => 'Campo requerido.',
            'clave.unique' => 'La clave ya existe.',
            'descripcion.required' => 'Campo requerido'
        ];
    }

    public function getNombreAttribute()
    {
        return $this->clave . ' | ' . $this->descripcion;
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
