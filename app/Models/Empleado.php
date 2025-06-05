<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Empleado
 * @package App\Models
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property integer id_empleado
 * @property string nombre
 * @property integer sucursal_id
 */
class Empleado extends Model
{
    use LogsActivity, SoftDeletes;

    public $table = 'tb_empleados';

    public $fillable = [
        'id_empleado',
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
        'id_empleado' => 'integer',
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
                    'created' => 'El Empleado ha sido creado.',
                    'updated' => 'El Empleado ha sido actualizado.',
                    'deleted' => 'El Empleado ha sido eliminado.',
                    'restored' => 'El Empleado ha sido restaurado.',
                    'forceDeleted' => 'El Empleado ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Empleados')
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
            'id_empleado' => ['required'],
            'nombre' => 'required',
            'sucursal_id' => ['required', 'exists:tb_sucursales,id']
        ];
    }

    public function messages()
    {
        return [
            'id_empleado.required' => 'Campo requerido.',
            'nombre.required' => 'Campo requerido.',
            'sucursal_id.required' => 'Campo requerido',
            'sucursal_id.exists' => 'Sucursal no encontrado',
        ];
    }

    public static function findById($id_empleado)
    {
        $resource = Empleado::where('id_empleado', $id_empleado)->get();
        if ($resource->count() > 0)
            return $resource->first();
        return null;
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
