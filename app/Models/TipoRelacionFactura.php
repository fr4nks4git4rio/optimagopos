<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TipoRelacionFactura
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version October 6, 2019, 12:01 pm CDT
 *
 * @property string codigo
 * @property string descripcion
 */
class TipoRelacionFactura extends Model
{
    use HasFactory, LogsActivity;

    public $table = 'tb_tipo_relacion_facturas';

    public $fillable = [
        'codigo',
        'descripcion'
    ];

    protected $appends = ['value', 'label'];

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
     * Validation rules
     *
     * @var array
     */
    public function rules()
    {
        return [
            'codigo' => ['required', Rule::unique('tb_tipo_relacion_facturas')->ignore($this->id)],
            'descripcion' => ['nullable']
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Campo requerido',
            'codigo.unique' => 'El Código ya existe.'
        ];
    }

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => 'El Tipo de Relación de Factura ha sido creado.',
                    'updated' => 'El Tipo de Relación de Factura ha sido actualizado.',
                    'deleted' => 'El Tipo de Relación de Factura ha sido eliminado.',
                    'restored' => 'El Tipo de Relación de Factura ha sido restaurado.',
                    'forceDeleted' => 'El Tipo de Relación de Factura ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Tipo de Relación de Factura')
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function getLabelAttribute()
    {
        return $this->codigo . ' - ' . $this->descripcion;
    }
}
