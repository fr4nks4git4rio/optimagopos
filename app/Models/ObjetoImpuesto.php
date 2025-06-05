<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ObjetoImpuesto
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:50 pm CST
 *
 * @property string clave
 * @property string descripcion
 * @property boolean activo
 */
class ObjetoImpuesto extends Model
{
    use LogsActivity;

    public $table = 'tb_objetos_impuesto';

    protected $appends = ['nombre', 'value', 'label'];

    public $fillable = [
        'clave',
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
        'clave' => 'string',
        'descripcion' => 'string',
        'activo' => 'boolean'
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
                    'created' => 'El Objeto de Impuesto ha sido creado.',
                    'updated' => 'El Objeto de Impuesto ha sido actualizado.',
                    'deleted' => 'El Objeto de Impuesto ha sido eliminado.',
                    'restored' => 'El Objeto de Impuesto ha sido restaurado.',
                    'forceDeleted' => 'El Objeto de Impuesto ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Objeto de Impuesto')
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
            'clave' => ['required', Rule::unique('tb_objetos_impuesto')->ignore($this->id)],
            'descripcion' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'clave.required' => 'Campo requerido.',
            'clave.unique' => 'La clave ya existe.',
            'descripcion.required' => 'Campo requerido.'
        ];
    }

    public function getNombreAttribute()
    {
        return $this->descripcion . ' (' . $this->clave . ')';
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
