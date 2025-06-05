<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Estado
 * @package App\Models\Administracion
 * @version May 6, 2019, 7:19 pm UTC
 *
 * @property string codigo
 * @property string nombre
 * @property boolean activo
 */
class Estado extends Model
{
    use LogsActivity;

    public $table = 'tb_estados';

    public $fillable = [
        'codigo',
        'nombre',
        'activo'
    ];

    protected $appends = ['label', 'value', 'text'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer'
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
                    'created' => 'El Estado ha sido creado.',
                    'updated' => 'El Estado ha sido actualizado.',
                    'deleted' => 'El Estado ha sido eliminado.',
                    'restored' => 'El Estado ha sido restaurado.',
                    'forceDeleted' => 'El Estado ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Estado')
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
            'codigo' => ['required', Rule::unique('tb_estados', 'codigo')->ignore($this->id)],
            'nombre' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Campo requerido',
            'codigo.unique' => 'El Código ya existe',
            'nombre.required' => 'Campo requerido',
        ];
    }

    public function getLabelAttribute()
    {
        return $this->nombre;
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function getTextAttribute()
    {
        return $this->codigo . ' - ' . $this->nombre;
    }

    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }

    public function localidades()
    {
        return $this->hasMany(Localidad::class);
    }
}
