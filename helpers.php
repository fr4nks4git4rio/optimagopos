<?php

use App\Models\Cliente;
use App\Models\Config;
use App\Models\Sucursal;
use App\Models\TipoCambio;
use App\Models\TipoCambioSistema;
use App\Services\Helpers\QuantityToWords;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
        if (!user())
            return null;
        return user()->cliente_id ? user()->owner : Cliente::where('es_propietario', 1)->first();
    }
}

if (!function_exists('get_system_owner')) {
    /**
     * @return Cliente|null
     */
    function get_system_owner()
    {
        return Cliente::where('es_propietario', 1)->first();
    }
}

if (!function_exists('active_route')) {
    /**
     * @param $route
     * @param string|array $output
     * @return mixed
     */
    function active_route($route, $output = 'active')
    {
        if (is_array($route)) {
            foreach ($route as $r) {
                if (Request::is($r))
                    return $output;
            }
        }
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
    function get_tipo_cambio($from_id, $to_id, $sucursal_id = null)
    {
        $tc = TipoCambio::where('from_id', $from_id)->where('to_id', $to_id)->where('sucursal_id', $sucursal_id ?: user()->cliente_id)->get();
        return $tc->count() > 0 ? $tc->first() : new TipoCambio();
    }
}

if (!function_exists('get_tipo_cambio_sistema')) {
    /**
     * @param null $date
     * @return TipoCambioSistema
     */
    function get_tipo_cambio_sistema($date = null)
    {
        $date = $date ?? Carbon::now()->format('Y-m-d');
        $change = TipoCambioSistema::whereRaw("DATE(created_at) = '$date'")
            ->get();

        return $change->count() > 0 ? $change->first() : new TipoCambioSistema();
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
    function modo_facturacion($sucursal_id)
    {
        return Sucursal::find($sucursal_id)?->cfdi_timbrado_productivo;
    }
}

if (!function_exists('modo_facturacion_sistema')) {
    function modo_facturacion_sistema()
    {
        return system_config('cfdi_timbrado_productivo');
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

if (!function_exists('pretty_message')) {
    function pretty_message($message, $type = 'success')
    {
        if ($type != 'success')
            Log::error($message);
        if (config('app.env') === 'production')
            return $type === 'success' ? 'Acción realizada correctamente!' : 'Lo sentimos. Ha ocurrido un error intentando realizar la acción.';

        return $message;
    }
}

if (!function_exists('extraer_datos_fiscales')) {
    function extraer_datos_fiscales($texto)
    {
        // Normalizar espacios
        $texto = preg_replace('/\s+/', ' ', $texto);

        // Insertar saltos de línea antes de campos clave (PF y PM)
        $campos = [
            'RFC:',
            'CURP:',
            'Denominación/RazónSocial:',
            'Nombre(s):',
            'Nombre (s):',
            'PrimerApellido:',
            'Primer Apellido:',
            'SegundoApellido:',
            'Segundo Apellido:',
            'RégimenCapital:',
            'Régimen Capital:',
            'NombreComercial:',
            'Fechainiciodeoperaciones:',
            'Estatusenelpadrón:',
            'Fechadeúltimocambiodeestado:',
            'CódigoPostal:',
            'TipodeVialidad:',
            'NombredeVialidad:',
            'NúmeroExterior:',
            'NúmeroInterior:',
            'NombredelaColonia:',
            'NombredelaLocalidad:',
            'NombredelMunicipiooDemarcaciónTerritorial:',
            'NombredelaEntidadFederativa:',
            'EntreCalle:',
            'YCalle:',
            'Actividades Económicas:',
            'Regímenes:'
        ];

        foreach ($campos as $campo) {
            $texto = str_replace($campo, "\n" . $campo, $texto);
        }

        $lineas = explode("\n", $texto);
        $datos = [];
        foreach ($lineas as $linea) {
            $linea = trim($linea);

            // RFC
            if (preg_match('/^RFC:\s*(.+)/', $linea, $m)) {
                $datos['rfc'] = trim($m[1]);
            }

            // CURP (Persona Física)
            elseif (preg_match('/^CURP:\s*(.+)/', $linea, $m)) {
                $datos['curp'] = trim($m[1]);
            }

            // Persona Moral
            elseif (preg_match('/^Denominación\/RazónSocial:\s*(.+)/i', $linea, $m)) {
                $datos['tipo_persona'] = 'moral';
                $datos['razon_social'] = trim($m[1]);
            }

            // Persona Moral
            elseif (preg_match('/^Nombre\s*Comercial:\s*(.+)/i', $linea, $m)) {
                $datos['nombre_comercial'] = trim($m[1]);
            }

            // Persona Física
            elseif (preg_match('/^Nombre\s*\(s\):\s*(.+)/i', $linea, $m)) {
                $datos['tipo_persona'] = 'fisica';
                $datos['nombre'] = trim($m[1]);
            } elseif (preg_match('/^Primer\s*Apellido:\s*(.+)/i', $linea, $m)) {
                $datos['apellido_paterno'] = trim($m[1]);
            } elseif (preg_match('/^Segundo\s*Apellido:\s*(.*)/i', $linea, $m)) {
                $datos['apellido_materno'] = trim($m[1]);
            }

            // Campos comunes
            elseif (preg_match('/^Fechainiciodeoperaciones:\s*(.+)/', $linea, $m)) {
                $datos['fecha_inicio_operaciones'] = trim($m[1]);
            } elseif (preg_match('/^Estatusenelpadrón:\s*(.+)/', $linea, $m)) {
                $datos['estatus_padron'] = trim($m[1]);
            } elseif (preg_match('/^CódigoPostal:\s*(\d{5})/', $linea, $m)) {
                $datos['codigo_postal'] = $m[1];
            } elseif (preg_match('/^NombredeVialidad:\s*(.+)/', $linea, $m)) {
                $datos['calle'] = trim($m[1]);
            } elseif (preg_match('/^NúmeroExterior:\s*(.+)/', $linea, $m)) {
                $datos['numero_exterior'] = trim($m[1]);
            } elseif (preg_match('/^NúmeroInterior:\s*(.*)/', $linea, $m)) {
                $datos['numero_interior'] = trim($m[1]);
            } elseif (preg_match('/^NombredelaColonia:\s*(.+)/', $linea, $m)) {
                $datos['colonia'] = trim($m[1]);
            } elseif (preg_match('/^NombredelaLocalidad:\s*(.+)/', $linea, $m)) {
                $datos['localidad'] = trim($m[1]);
            } elseif (preg_match('/^NombredelMunicipiooDemarcaciónTerritorial:\s*(.+)/', $linea, $m)) {
                $datos['municipio'] = trim($m[1]);
            } elseif (preg_match('/^NombredelaEntidadFederativa:\s*(.+)/', $linea, $m)) {
                $datos['estado'] = trim($m[1]);
            } elseif (preg_match('/^Actividades Económicas:\s*(.*)/', $linea, $m)) {
                $datos['actividades_economicas'] = trim($m[1]);
            }
        }

        // Extraer régimen fiscal
        if (preg_match('/Regímenes:(.*?)Obligaciones:/', $texto, $bloqueRegimen)) {
            $regimenesTexto = $bloqueRegimen[1];
            $lineasRegimen = preg_split('/\s*Régimen\s+/i', $regimenesTexto);

            $regimenesValidos = [];

            foreach ($lineasRegimen as $linea) {
                if (preg_match('/^(.+?)\s+\d{2}\/\d{2}\/\d{4}/', trim($linea), $m)) {
                    $regimen = trim($m[1]);

                    // Quitar "de las " solo si está al inicio
                    $regimen = preg_replace('/^de\s+las\s+/i', '', $regimen);
                    $regimenesValidos[] = $regimen;
                }
            }

            if (count($regimenesValidos)) {
                $datos['regimen_fiscal'] = end($regimenesValidos);
            }
        }

        return $datos;
    }

    if (!function_exists('ticket_vk_status_str')) {
        function ticket_vk_status_str(int $status)
        {
            return [
                1 => 'Open',
                2 => 'InProcess',
                3 => 'Done',
                4 => 'Delayed'
            ][$status];
        }
    }
}
