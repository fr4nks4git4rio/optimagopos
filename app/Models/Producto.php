<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Producto
 * @package App\Models
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property integer id_producto
 * @property string nombre
 * @property float precio
 * @property integer sucursal_id
 */
class Producto extends Model
{
    use LogsActivity, SoftDeletes;

    public $table = 'tb_productos';

    public $fillable = [
        'id_producto',
        'nombre',
        'precio',
        'sucursal_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_producto' => 'integer',
        'nombre' => 'string',
        'precio' => 'float',
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
                    'created' => 'El Producto ha sido creado.',
                    'updated' => 'El Producto ha sido actualizado.',
                    'deleted' => 'El Producto ha sido eliminado.',
                    'restored' => 'El Producto ha sido restaurado.',
                    'forceDeleted' => 'El Producto ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Productos')
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
            'id_producto' => ['required'],
            'nombre' => 'required',
            'precio' => 'required',
            'sucursal_id' => ['required', 'exists:tb_sucursales,id']
        ];
    }

    public function messages()
    {
        return [
            'id_producto.required' => 'Campo requerido.',
            'nombre.required' => 'Campo requerido.',
            'precio.required' => 'Campo requerido.',
            'sucursal_id.required' => 'Campo requerido',
            'sucursal_id.exists' => 'Sucursal no encontrada',
        ];
    }
}
