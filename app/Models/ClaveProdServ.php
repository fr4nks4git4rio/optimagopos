<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ClaveProdServ
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:53 pm CST
 *
 * @property string codigo
 * @property string nombre
 * @property string descripcion
 * @property number valor_unitario
 * @property boolean activo
 * @property integer clave_unidad_id
 */
class ClaveProdServ extends Model
{
    public $table = 'tb_clave_prod_servs';

    protected $appends = ['nombre_completo', 'label', 'text', 'value'];

    public $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'valor_unitario',
        'activo',
        'clave_unidad_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'codigo' => 'string',
        'nombre' => 'string',
        'descripcion' => 'string',
        'valor_unitario' => 'float',
        'activo' => 'boolean',
        'clave_unidad_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public function rules()
    {
        return [
            'codigo' => ['required', Rule::unique('tb_clave_prod_servs')->ignore($this->id)],
            'nombre' => ['required', Rule::unique('tb_clave_prod_servs')->ignore($this->id)],
            'valor_unitario' => ['required'],
            'clave_unidad_id' => ['required', 'exists:tb_clave_unidades,id'],
            'descripcion' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Campo requerido.',
            'codigo.unique' => 'El código ya existe.',
            'nombre.required' => 'Campo requerido.',
            'nombre.unique' => 'El nombre ya existe.',
            'valor_unitario.required' => 'Campo requerido.',
            'clave_unidad_id.required' => 'Campo requerido.',
            'clave_unidad_id.exists' => 'Clave Unidad no reconocida.',
        ];
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre .' ('. $this->codigo .')';
    }

    public function getTextAttribute()
    {
        return $this->nombre_completo;
    }

    public function getLabelAttribute()
    {
        return $this->nombre_completo;
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public static function findByCodigo($codigo){
        return ClaveProdServ::where('codigo', $codigo)->where('activo', 1)->first();
    }

    public function clave_unidad()
    {
        return $this->belongsTo(ClaveUnidad::class);
    }
}
