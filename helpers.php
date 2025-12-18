<?php

use App\Models\Cliente;
use App\Models\Config;
use App\Models\TipoCambio;
use App\Services\Helpers\QuantityToWords;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;

/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 9/9/2021
 * Time: 08:46
 */

if (!function_exists('user')) {
    /**
     * @return Authenticatable|null
     */
    function user()
    {
        return auth()->user();
    }
}

if (!function_exists('get_owner')) {
    /**
     * @return Cliente|null
     */
    function get_owner()
    {
        return user() ? user()->owner : null;
    }
}

if (!function_exists('active_route')) {
    /**
     * @param $route
     * @param string $output
     * @return mixed
     */
    function active_route($route, string $output = 'active')
    {
        if (Request::is($route))
            return $output;
    }
}

if (!function_exists('get_mes_es_simple')) {
    /**
     * @param $mes_int
     * @return string
     */
    function get_mes_es_simple($mes_int)
    {
        $meses = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        return $meses[(int)$mes_int];
    }
}

if (!function_exists('get_mes_es')) {
    /**
     * @param $mes_int
     * @return string
     */
    function get_mes_es($mes_int)
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return $meses[$mes_int];
    }
}

if (!function_exists('convertir_numero_a_letras')) {
    /**
     * @param $number
     * @param $currency
     * @return string
     */
    function convertir_numero_a_letras($number, $currency = 'MXN')
    {
        $currency = strtoupper($currency) === 'USD' ? 'USD' : 'MXN';
        if (str_contains($number, '.')) {
            list($integer, $decimals) = explode('.', $number);
        } else {
            $integer = $number;
            $decimals = 0;
        }

        $converted = QuantityToWords::convertir($integer, $currency === 'USD' ? 'DOLARES' : 'PESOS');

        $cents = $decimals . "/100 " . strtoupper($currency);

        $converted .= " " . $cents;

        return $converted;
    }
}

if (!function_exists('system_config')) {
    function system_config($key, $valor = null)
    {
        if ($valor !== null) {
            Config::updateOrCreate([
                'llave' => $key,
            ], [
                'valor' => $valor
            ]);
            return $valor;
        }
        return Config::firstOrCreate(['llave' => $key])->valor;
    }
}

if (!function_exists('system_iva')) {
    function system_iva($valor = null)
    {
        if ($valor)
            return system_config('iva', $valor);
        return system_config('iva');
    }
}

if (!function_exists('get_tipo_cambio')) {
    /**
     * @param $date | null
     * @return TipoCambio
     */
    function get_tipo_cambio($date = null, $cliente_id = null)
    {
        if (now()->hour < 8) {
            return TipoCambio::where('cliente_id', $cliente_id ?: user()->cliente_id)
                ->orderBy('id', 'desc')->first();
        } else {
            $date = $date ?? Carbon::now()->format('Y-m-d');
            $change = TipoCambio::where('cliente_id', $cliente_id ?: user()->cliente_id)
                ->whereRaw("DATE(created_at) = '$date'")
                ->get();

            return $change->count() > 0 ? $change->first() : new TipoCambio();
        }
    }
}

if (!function_exists('flash_message')) {
    /**
     * @param $type
     * @param $message
     * @return void
     */
    function flash_message($message, $type = 'info'): void
    {
        session()->put('flashMessage', $message);
        session()->put('flashType', $type);
    }
}

if (!function_exists('flash_success')) {
    function flash_success($message): void
    {
        flash_message($message, 'success');
    }
}

if (!function_exists('flash_danger')) {
    function flash_danger($message): void
    {
        flash_message($message, 'danger');
    }
}

if (!function_exists('fmod_positive_only')) {
    function fmod_positive_only($x, $y)
    {
        return $x - floor($x / $y) * $y;
    }
}

if (!function_exists('modo_facturacion')) {
    function modo_facturacion()
    {
        return Config::firstOrCreate([
            'llave' => 'cfdi_timbrado_productivo'
        ], [
            'valor' => 0
        ])->valor;
    }
}

if (!function_exists('download_xml')) {
    function download_xml($src, $name)
    {
        header('Content-Type: application/x-download');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo file_get_contents($src);
        exit;
    }
}

if (!function_exists('get_estados_mexico')) {
    function get_estados_mexico()
    {
        return [
            'Aguascalientes',
            'Baja California',
            'Baja California Sur',
            'Campeche',
            'Chiapas',
            'Chihuahua',
            'Ciudad de México',
            'Coahuila',
            'Colima',
            'Durango',
            'Estado de México',
            'Guanajuato',
            'Guerrero',
            'Hidalgo',
            'Jalisco',
            'Michoacán',
            'Morelos',
            'Nayarit',
            'Nuevo León',
            'Oaxaca',
            'Puebla',
            'Querétaro',
            'Quintana Roo',
            'San Luis Potosí',
            'Sinaloa',
            'Sonora',
            'Tabasco',
            'Tamaulipas',
            'Tlaxcala',
            'Veracruz',
            'Yucatán',
            'Zacatecas'
        ];
    }
}

if (!function_exists('get_dimensiones_opt')) {
    function get_dimensiones_opt()
    {
        return [
            '2.44 x 12.19 x 2.59',
            '2.53 x 16.15 x 2.77'
        ];
    }
}

if (!function_exists('pretty_message')) {
    function pretty_message($message, $type = 'success')
    {
        if (config('app.env') === 'production')
            return $type === 'success' ? 'Acción realizada correctamente!' : 'Lo sentimos. Ha ocurrido un error intentando realizar la acción.';

        return $message;
    }
}
