<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class UbicacionVk
 * @package App\Models
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property integer $id_ubicacion
 * @property string $nombre
 * @property integer $sucursal_id
 */
class UbicacionVk extends Model
{
    use LogsActivity, SoftDeletes;

    public $table = 'tb_ubicaciones_vk';

    public $fillable = [
        'id_ubicacion',
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
        'id_ubicacion' => 'string',
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
                    'created' => 'La Ubicación Video Kitchen ha sido creado.',
                    'updated' => 'La Ubicación Video Kitchen ha sido actualizado.',
                    'deleted' => 'La Ubicación Video Kitchen ha sido eliminado.',
                    'restored' => 'La Ubicación Video Kitchen ha sido restaurado.',
                    'forceDeleted' => 'La Ubicación Video Kitchen ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Ubicaciones Video Kitchen')
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
            'id_ubicacion' => ['required'],
            'nombre' => 'nullable',
            'sucursal_id' => ['required', 'exists:tb_sucursales,id']
        ];
    }

    public function messages()
    {
        return [
            'id_ubicacion.required' => 'Campo requerido.',
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
