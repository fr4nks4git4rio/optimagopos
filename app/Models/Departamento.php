<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Departamento
 * @package App\Models
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property integer id_departamento
 * @property string nombre
 * @property integer sucursal_id
 */
class Departamento extends Model
{
    use LogsActivity, SoftDeletes;

    public $table = 'tb_departamentos';

    public $fillable = [
        'id_departamento',
        'nombre',
        'sucursal_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_departamento' => 'integer',
        'nombre' => 'string',
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
                    'created' => 'El Departamento ha sido creado.',
                    'updated' => 'El Departamento ha sido actualizado.',
                    'deleted' => 'El Departamento ha sido eliminado.',
                    'restored' => 'El Departamento ha sido restaurado.',
                    'forceDeleted' => 'El Departamento ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Departamentos')
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
            'id_departamento' => ['required'],
            'nombre' => 'required',
            'sucursal_id' => ['required', 'exists:tb_sucursales,id']
        ];
    }

    public function messages()
    {
        return [
            'id_departamento.required' => 'Campo requerido.',
            'nombre.required' => 'Campo requerido.',
            'sucursal_id.required' => 'Campo requerido',
            'sucursal_id.exists' => 'Sucursal no encontrado',
        ];
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
