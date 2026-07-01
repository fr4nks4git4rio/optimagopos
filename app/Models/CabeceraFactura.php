<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CabeceraFactura extends Model
{
    use HasFactory;

    public $table = 'tb_cabecera_factura';

    public $timestamps = false;

    public $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'correo',
        'telefono',
        'portal_pac',
        'usuario_integrador_sat',
        'calle',
        'no_exterior',
        'no_interior',
        'codigo_postal',
        'colonia',
        'referencia',
        'regimen_fiscal_id',
        'estado_id',
        'localidad_id',
        'municipio_id'
    ];

    public static function parseData($data = [])
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['regimen_fiscal_id']) && $value) {
                switch ($key) {
                    case 'regimen_fiscal_id':
                        $data['regimen_fiscal'] = DB::table('tb_regimen_fiscales')
                            ->selectRaw('id, CONCAT(codigo, " - ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'estado_id':
                        $data['estado'] = DB::table('tb_estados')
                            ->selectRaw('id, nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'localidad_id':
                        $data['estado'] = DB::table('tb_localidades')
                            ->selectRaw('id, nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'municipio_id':
                        $data['estado'] = DB::table('tb_municipios')
                            ->selectRaw('id, nombre')->where('id', $value)->first()->nombre;
                        break;
                }
            }
        }

        return $data;
    }
}
