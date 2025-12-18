<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TipoCambio
 * @package App\Models\Administracion
 * @version May 6, 2019, 7:37 pm UTC
 *
 * @property float $tasa
 * @property integer $cliente_id
 */
class TipoCambio extends Model
{
    use LogsActivity;

    public $table = 'tb_tipo_cambios';

    public $fillable = [
        'tasa',
        'cliente_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tasa' => 'float',
        'cliente_id' => 'integer'
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
                    'created' => 'El Tipo de Cambio ha sido creado.',
                    'updated' => 'El Tipo de Cambio ha sido actualizado.',
                    'deleted' => 'El Tipo de Cambio ha sido eliminado.',
                    'restored' => 'El Tipo de Cambio ha sido restaurado.',
                    'forceDeleted' => 'El Tipo de Cambio ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Tipo de Cambio')
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'tasa' => 'required',
        'cliente_id' => 'required'
    ];

    /**
     * @param $date string Fecha formateada con el formato: Y-m-d
     * @return mixed|null
     */
    public static function getTipoCambioFecha($date, $cliente_id = null)
    {
        $tipo_cambio = TipoCambio::where('cliente_id', $cliente_id ?: user()->cliente_id)
            ->whereRaw("DATE(created_at) = '" . $date . "'")->get();

        return $tipo_cambio->count() > 0 ? $tipo_cambio->last() : new TipoCambio();
    }

    public static function CreateOrUpdate($value, $cliente_id = null)
    {
        $element = TipoCambio::where('cliente_id', $cliente_id ?: user()->cliente_id)
            ->whereRaw("DATE(created_at) = '" . today()->format('Y-m-d') . "'")->first();
        if ($element)
            $element->update([
                'tasa' => $value
            ]);
        else
            $element = TipoCambio::create([
                'tasa' => $value,
                'cliente_id' => $cliente_id ?: user()->cliente_id
            ]);
        return $element;
    }

    public static function obtenerTipoCambioUrl($cliente_id = null)
    {
        date_default_timezone_set('America/Cancun');

        $tipo_cambio = get_tipo_cambio(null, $cliente_id);
        if (!$tipo_cambio->id) {
            $hoy = Carbon::today();
            if ($hoy->isSunday()) $hoy->subDays(2);
            if ($hoy->isSaturday()) $hoy->subDay();

            $dd = $hoy->format('d');
            $mm = $hoy->format('m');
            $yyyy = $hoy->format('Y');
            try {
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
                        $change_type = TipoCambio::create([
                            'tasa' => $tipo_cambio,
                            'cliente_id' => $cliente_id ?: user()->cliente_id
                        ]);
                        return $change_type;
                    }
                } else {
                    return 'Ocurrió un error obteniendo el Tipo de Cambio del DOF. Tipo de Cambio no encontrado.';
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
