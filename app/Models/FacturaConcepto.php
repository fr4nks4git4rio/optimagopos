<?php

namespace App\Models;

use App\Models\ClaveProdServ;
use App\Models\ClaveUnidad;
use App\Models\ObjetoImpuesto;
use App\Models\ServicioFijo;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FacturaConcepto
 * @package App\Models\Ventas
 * @version October 6, 2019, 12:01 pm CDT
 *
 * @property float $cantidad
 * @property float $precio_unitario
 * @property string $descripcion
 * @property integer $factura_id
 * @property integer $clave_prod_serv_id
 * @property integer $clave_unidad_id
 * @property integer $objeto_impuesto_id
 */
class FacturaConcepto extends Model
{
    public $table = 'tb_factura_conceptos';

    protected $appends = ['importe', 'iva'];

    public $fillable = [
        'cantidad',
        'precio_unitario',
        'descripcion',
        'factura_id',
        'clave_prod_serv_id',
        'clave_unidad_id',
        'objeto_impuesto_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cantidad' => 'float',
        'precio_unitario' => 'float',
        'descripcion' => 'string',
        'factura_id' => 'integer',
        'clave_prod_serv_id' => 'integer',
        'clave_unidad_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'cantidad' => 'required',
        'precio_unitario' => 'required',
        'factura_id' => 'required',
        'clave_prod_serv_id' => 'required',
        'clave_unidad_id' => 'required',
    ];

    public function getImporteAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    public function getIvaAttribute()
    {
        return $this->importe * max($this->factura->porciento_iva, 0) / 100;
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function clave_prod_serv()
    {
        return $this->belongsTo(ClaveProdServ::class);
    }

    public function clave_unidad()
    {
        return $this->belongsTo(ClaveUnidad::class);
    }

    public function objeto_impuesto()
    {
        return $this->belongsTo(ObjetoImpuesto::class);
    }
}
