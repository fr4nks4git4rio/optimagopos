<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class RegimenFiscal
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:54 pm CST
 *
 * @property string codigo
 * @property string descripcion
 * @property boolean activo
 */
class RegimenFiscal extends Model
{
    use LogsActivity;

    public $table = 'tb_regimen_fiscales';

    protected $appends = ['nombre', 'label', 'value'];

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
                    'created' => 'El Régimen Fiscal ha sido creado.',
                    'updated' => 'El Régimen Fiscal ha sido actualizado.',
                    'deleted' => 'El Régimen Fiscal ha sido eliminado.',
                    'restored' => 'El Régimen Fiscal ha sido restaurado.',
                    'forceDeleted' => 'El Régimen Fiscal ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Régimen Fiscal')
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
            'codigo' => ['required', Rule::unique('tb_regimen_fiscales')->ignore($this->id)],
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

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function getLabelAttribute()
    {
        return $this->nombre;
    }

    public function getNombreAttribute()
    {
        return $this->descripcion . ' (' . $this->codigo . ')';
    }
    public static function findByCode($code){
        return self::where('codigo', $code)->where('activo', 1)->first();
    }
}
