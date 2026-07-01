<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class TipoCambio
 * @package App\Models\Administracion
 * @version May 6, 2019, 7:37 pm UTC
 *
 * @property float $tasa
 */
class TipoCambioSistema extends Model
{
    public $table = 'tb_tipo_cambios_sistema';

    public $fillable = [
        'tasa'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tasa' => 'float'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'tasa' => 'required'
    ];

    /**
     * @param $date string Fecha formateada con el formato: Y-m-d
     * @return mixed|null
     */
    public static function getTipoCambioFecha($date)
    {
        $tipo_cambio = TipoCambioSistema::whereRaw("DATE(created_at) = '" . $date . "'")->get();

        return $tipo_cambio->count() > 0 ? $tipo_cambio->last() : new TipoCambioSistema();
    }

    public static function CreateOrUpdate($value)
    {
        $element = TipoCambioSistema::whereRaw("DATE(created_at) = '" . today()->format('Y-m-d') . "'")->first();
        if ($element)
            $element->update(['tasa' => $value]);
        else
            $element = TipoCambioSistema::create([
                'tasa' => $value
            ]);
        return $element;
    }

    public static function obtenerTipoCambioUrl()
    {
        date_default_timezone_set('America/Cancun');

        $tipo_cambio = get_tipo_cambio_sistema();
        if (!$tipo_cambio->id) {
            try {
                $max_intentos = 7;
                $existe = false;
                while (!$existe && $max_intentos > 0) {
                    $fecha = today();
                    if ($fecha->isSunday()) $fecha->subDays(2);
                    if ($fecha->isSaturday()) $fecha->subDay();
                    $dd = $fecha->format('d');
                    $mm = $fecha->format('m');
                    $yyyy = $fecha->format('Y');
                    $url = "https://dof.gob.mx/indicadores_detalle.php?cod_tipo_indicador=158&dfecha=$dd%2F$mm%2F$yyyy&hfecha=$dd%2F$mm%2F$yyyy";
                    $arrContextOptions = array(
                        "ssl" => array(
                            "verify_peer" => false,
                            "verify_peer_name" => false,
                        ),
                    );
                    $site = file_get_contents($url, false, stream_context_create($arrContextOptions));
                    $existe = preg_match('/\d{2}[.]\d{6}/', $site, $coincidencias);
                    if ($existe && count($coincidencias) == 1) {
                        $tipo_cambio = $coincidencias[0];
                        if ($tipo_cambio && floatval($tipo_cambio)) {
                            $change_type = TipoCambioSistema::create([
                                'tasa' => $tipo_cambio
                            ]);
                            return $change_type;
                        }
                    }
                    $max_intentos--;
                    $fecha->subDay();
                    sleep(1);
                }
                if (!$existe) {
                    $ct = TipoCambioSistema::orderBy('id', 'desc')->first();
                    $change_type = TipoCambioSistema::create([
                        'tasa' => $ct->tasa
                    ]);
                    return $change_type;
                }
            } catch (\Exception $e) {
                Log::log('warning', "Error en la consulta. " . $e->getMessage());
                return 'Ocurrió un error obteniendo el Tipo de Cambio del DOF. Verifique su conexión a internet.' . $e->getMessage();
            }
        } else {
            return 'El Tipo de Cambio ya existe.';
        }
    }
}
