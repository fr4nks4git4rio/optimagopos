<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Serie
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property string descripcion
 * @property boolean activo
 */
class Serie extends Model
{
    use LogsActivity;

    public $table = 'tb_series';

    public $fillable = [
        'descripcion',
        'activo'
    ];

    protected $appends = ['label','text', 'value'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
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
                    'created' => 'La Serie ha sido creada.',
                    'updated' => 'La Serie ha sido actualizada.',
                    'deleted' => 'La Serie ha sido eliminada.',
                    'restored' => 'La Serie ha sido restaurada.',
                    'forceDeleted' => 'La Serie ha sido eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Serie')
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
            'descripcion' => ['required', Rule::unique('tb_series')->ignore($this->id)]
        ];
    }

    public function messages()
    {
        return [
            'descripcion.required' => 'Campo requerido.',
            'descripcion.unique' => 'La Serie ya existe.',
        ];
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function getLabelAttribute()
    {
        return $this->descripcion;
    }
    public function getTextAttribute()
    {
        return $this->descripcion;
    }
}
