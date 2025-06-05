<?php

namespace App\Models;

use App\Models\Ventas\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Direccion
 * @package App\Models\Directorio
 * @version October 8, 2019, 7:49 am CDT
 *
 * @property string calle
 * @property string no_exterior
 * @property string no_interior
 * @property string codigo_postal
 * @property string colonia
 * @property integer localidad_id
 * @property integer municipio_id
 * @property integer estado_id
 * @property string referencia
 */
class Direccion extends Model
{
    public $table = 'tb_direcciones';

    protected $appends = ['direccion_formateada'];

    public $fillable = [
        'calle',
        'no_exterior',
        'no_interior',
        'codigo_postal',
        'colonia',
        'localidad_id',
        'municipio_id',
        'estado_id',
        'referencia'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'calle' => 'string',
        'no_exterior' => 'string',
        'no_interior' => 'string',
        'codigo_postal' => 'string',
        'colonia' => 'string',
        'localidad_id' => 'integer',
        'municipio_id' => 'integer',
        'estado_id' => 'integer',
        'codigo_estado' => 'string',
        'referencia' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    public static function parseData($data = [])
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['localidad_id', 'municipio_id', 'estado_id']) && $value) {
                switch ($key) {
                    case 'localidad_id':
                        $data['localidad'] = DB::table('tb_localidades')
                            ->selectRaw('id, CONCAT(nombre, " (", codigo, ")") as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'municipio_id':
                        $data['municipio'] = DB::table('tb_municipios')
                            ->selectRaw('id, CONCAT(nombre, " (", codigo, ")") as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'estado_id':
                        $data['estado'] = DB::table('tb_estados')
                            ->selectRaw('id, CONCAT(nombre, " (", codigo, ")") as nombre')->where('id', $value)->first()->nombre;
                        break;
                }
            }
        }

        return $data;
    }

    public function getDireccionFormateadaAttribute()
    {
        return $this->calle .
            ($this->no_exterior ? (', No. Ext.: ' . $this->no_exterior) : '') .
            ($this->no_interior ? (', No. Int.: ' . $this->no_interior) : '') .
            ($this->codigo_postal ? (', CP: ' . $this->codigo_postal) : '') .
            ($this->colonia ? (', Colonia: ' . $this->colonia) : '') .
            ($this->localidad()->exists() ? (', Localidad: ' . $this->localidad()->first()->nombre) : '') .
            ($this->localidad()->exists() ? (' (' . $this->localidad()->first()->codigo . ')') : '') .
            ($this->municipio()->exists() ? (', Municipio: ' . $this->municipio()->first()->nombre) : '') .
            ($this->municipio()->exists() ? (' (' . $this->municipio()->first()->codigo . ')') : '') .
            ($this->estado()->exists() ? (', Estado: ' . $this->estado()->first()->nombre) : '') .
            ($this->estado()->exists() ? (' (' . $this->estado()->first()->codigo . ')') : '') .
            ($this->referencia ? (', Referencia: ' . $this->referencia) : '');
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'localidad_id', 'id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id', 'id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }
}
