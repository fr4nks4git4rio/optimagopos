<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Municipio
 * @package App\Models\Administracion
 * @version May 6, 2019, 7:19 pm UTC
 *
 * @property string codigo
 * @property string nombre
 * @property boolean activo
 * @property integer estado_id
 */
class Municipio extends Model
{
    public $table = 'tb_municipios';

    public $fillable = [
        'codigo',
        'nombre',
        'activo',
        'estado_id'
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
     * Validation rules
     *
     * @var array
     */
    public function rules()
    {
        return [
            'codigo' => 'required',
            'nombre' => 'required',
            'estado_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Campo requerido',
            'nombre.required' => 'Campo requerido',
            'estado_id.required' => 'Campo requerido'
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

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
