<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ClaveUnidad
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:50 pm CST
 *
 * @property string codigo
 * @property string descripcion
 * @property boolean activo
 */
class ClaveUnidad extends Model
{
    use LogsActivity;

    public $table = 'tb_clave_unidades';

    protected $appends = ['nombre', 'value', 'label'];

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
        'descripcion' => 'string',
        'activo' => 'boolean',
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
                    'created' => 'La Clave Unidad ha sido creada.',
                    'updated' => 'La Clave Unidad ha sido actualizada.',
                    'deleted' => 'La Clave Unidad ha sido eliminada.',
                    'restored' => 'La Clave Unidad ha sido restaurada.',
                    'forceDeleted' => 'La Clave Unidad ha sido eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Clave Unidad')
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
            'codigo' => ['required', Rule::unique('tb_clave_unidades')->ignore($this->id)],
            'descripcion' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Campo requerido.',
            'codigo.unique' => 'El código ya existe.',
            'descripcion.required' => 'Campo requerido.'
        ];
    }

    public function getNombreAttribute()
    {
        return $this->descripcion .' ('.$this->codigo.')';
    }

    public function getLabelAttribute()
    {
        return $this->nombre;
    }

    public function getValueAttribute()
    {
        return $this->id;
    }
}
