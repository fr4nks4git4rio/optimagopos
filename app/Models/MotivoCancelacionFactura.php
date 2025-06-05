<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MotivoCancelacionFactura
 * @package App\Models\Administracion
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string codigo
 * @property string descripcion
 */
class MotivoCancelacionFactura extends Model
{
    public $table = 'tb_motivos_cancelacion_factura';

    protected $appends = ['nombre', 'label', 'value'];

    public $fillable = [
        'codigo',
        'descripcion',
        'modulo_id'
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
        'modulo_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'codigo' => 'required',
        'descripcion' => 'required'
    ];

    public function getNombreAttribute()
    {
        return $this->codigo . ($this->descripcion ? (' | ' . $this->descripcion) : '');
    }

    public function getLabelAttribute()
    {
        return $this->nombre;
    }

    public function getValueAttribute()
    {
        return $this->id;
    }
}
