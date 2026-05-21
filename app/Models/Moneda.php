<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Moneda
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string $codigo
 * @property string $descripcion
 * @property boolean $activo
 */
class Moneda extends Model
{
    use LogsActivity, SoftDeletes;

    public $table = 'tb_monedas';

    protected $appends = ['value', 'label', 'text'];

    public $fillable = [
        'acronimo',
        'nombre'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'acronimo' => 'string',
        'nombre' => 'string'
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
                    'created' => 'La Moneda ha sido creada.',
                    'updated' => 'La Moneda ha sido actualizada.',
                    'deleted' => 'La Moneda ha sido eliminada.',
                    'restored' => 'La Moneda ha sido restaurada.',
                    'forceDeleted' => 'La Moneda ha sido eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Moneda')
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
            'acronimo' => ['required', Rule::unique('tb_monedas')->ignore($this->id)],
            'nombre' => ['required', Rule::unique('tb_monedas')->ignore($this->id)]
        ];
    }

    public function messages()
    {
        return [
            'acronimo.required' => 'Campo obligatorio.',
            'acronimo.unique' => 'El acrónimo ya existe.',
            'nombre.required' => 'Campo obligatorio.',
            'nombre.unique' => 'El nombre ya existe.'
        ];
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function getLabelAttribute()
    {
        return $this->acronimo;
    }

    public function getTextAttribute()
    {
        return $this->acronimo;
    }
}
