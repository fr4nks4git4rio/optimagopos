<?php

namespace App\Services\Helpers;

use App\Models\Administracion\Config;
use App\Models\Administracion\TipoCambio;
use App\Models\Ventas\Factura;
use App\Models\Ventas\Empresa;
use App\Models\Administracion\CodificadoresFacturacion\Serie;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 4/29/2019
 * Time: 2:04 PM
 */
class Helper
{

    /**Metodo que gestiona los permisos de un usuario en el sistema, estos permisos estaran dados por
     * los roles que tenga asignado el usuario, los modulos que tienen asignados cada rol y los permisos
     * de cada rol sobre dichos modulos
     *
     * @param \App\User $user Usuario
     * @param $mod integer Modulo ['Cotizador' => 1, 'Directorio' => 2, 'Compras' => 3, 'Facturacion' => 4,
     *                            'Administracion' => 5, 'Recursos Humanos' => 6]
     * @param null $accion ['read', 'create', 'update', 'delete'] Si no se selecciona una accion de las listadas
     *                      anteriormente se infiere que se desea acceder al módulo.
     * @return bool
     */
    public static function _can(User $user, $mod, $accion = null)
    {
        $rols = $user->rols;
        if ($accion) {
            //TODO Verificacion para cuando se quiere realizar alguna accion como son['read', 'create', 'update', 'delete']

            $can = false;
            $rols->each(function ($rol) use (&$can, $accion, $mod) {
                $rol->modulos->each(function ($modulo) use (&$can, $accion, $mod) {
                    if ($modulo->modulo_id === (int)$mod) {
                        $permiso = $modulo->permisos->where('llave', strtolower($accion));
                        if ($permiso->count() > 0 && $permiso->first()->valor == 1) {
                            $can = true;
                            return;
                        }
                        return;
                    }
                });
            });
            return $can;
        }

        //TODO Verificacion para cuando se quiere acceder de forma visual a alguno de los modulos

        $modulos_ids = [];
        $rols->each(function ($rol) use (&$modulos_ids) {
            $rol->modulos->each(function ($modulo) use (&$modulos_ids) {
                if (!in_array($modulo->modulo->id, $modulos_ids, false)) {
                    $modulos_ids[] = $modulo->modulo->id;
                }
            });
        });
        if (in_array($mod, $modulos_ids, false)) {
            return true;
        }
        return false;
    }

    /**
     * @param User|null $user_param
     * @param array|integer $rol_id {Administracion => 1, Contabilidad => 2, Venta => 3, Operacion => 4}
     * @return bool
     */
    public static function _hasRole($rol_id, User $user_param = null): bool
    {
        $user = $user_param ?: user();
        $has = false;
        if (is_array($rol_id)) {
//            dd($rol_id, $user->rols);
            $user->rols->map(function ($rol) use (&$has, $rol_id) {
                if (in_array($rol->id, $rol_id, true)) {
                    $has = true;
                }
            });
        } else {
            $user->rols->map(function ($rol) use (&$has, $rol_id) {
                if ($rol->id === $rol_id)
                    $has = true;
            });
        }

        return $has;
    }

    public static function modulosLists()
    {
        $modulos = Modulo::all();
        $pluked = $modulos->pluck('nombre', 'id');

        return $pluked;
    }

    public static function set_message($message = 'Error', $type = 'warning')
    {
        Session::put('flash_message', $message);
        Session::put('flash-warning', $type);
    }

    /**
     * @param null $date format: Y-m-d
     * @return TipoCambio
     */
    public static function getTipoCambio($date = null)
    {
        $date = $date ?? Carbon::now()->format('Y-m-d');
        $change = TipoCambio::whereRaw("DATE(created_at) = '$date'")
            ->get();

        return $change->count() > 0 ? $change->first() : new TipoCambio();
    }

    public static function getNombreProformaCorreo($key)
    {
        $values = [
            'enviar_cotizacion_cliente' => 'Enviar la Cotización al Cliente'
        ];

        return $values[$key];
    }

    private static function getEtiquetasProformaCorreo()
    {
        return [
            '{nombre_cliente}',
            '{correo_cliente}',
            '{nombre_vendedor}'
        ];
    }

    public static function getVendedoresCombo()
    {
        $vendedores = collect();
        User::all()->map(function ($user) use (&$vendedores) {
            $vendedor = collect();
            $vendedor->id = $user->id;
            $vendedor->nombre = $user->nombre_completo;
            $vendedores->push($vendedor);
        });

        return $vendedores->pluck('nombre', 'id')->prepend('Todos', -1)->prepend('Seleccione...', '');
    }

    /**
     * @param boolean $is_complement - default -> false
     * @param bool $is_nota_credito
     * @param bool $is_nomina
     * @param bool $is_nota_venta
     * @param boolean $modo_productivo
     * @return int
     */
    public static function internalSheetGenerator($propietario, $modo_productivo = false, $is_complement = false, $is_nota_credito = false, $is_nomina = false, $is_nota_venta = false, $is_carta_porte = false)
    {
        $query = Factura::query();
        if ($is_nomina)
            $query->where('es_nomina', 1)
                ->whereIn('estado', ['TIMBRADA', 'CANCELADA', 'COBRADA']);
        elseif ($is_nota_credito) {
            $query->where('es_nota_credito', 1)
                ->whereIn('estado', ['TIMBRADA', 'CANCELADA', 'COBRADA']);
        } elseif ($is_complement) {
            $query->where('es_complemento', 1)
                ->whereIn('estado', ['TIMBRADA', 'CANCELADA', 'COBRADA']);
        } elseif ($is_nota_venta) {
            $query->where('es_nota_venta', 1);
        } else {
            $query->where('es_complemento', 0)
                ->where('es_nota_credito', 0)
                ->where('es_nomina', 0)
                ->where('es_nota_venta', 0)
                ->where('es_nota_debito', 0)
                ->whereIn('estado', ['TIMBRADA', 'CANCELADA', 'COBRADA']);
        }
        if ($modo_productivo) {
            $factura_complemento = $query->where('propietario_id', $propietario)
                ->where('folio_interno', '!=', null)
                ->where(function ($q) {
                    $q->where('modo_prueba_cfdi', null)
                        ->orWhere('modo_prueba_cfdi', 0);
                })
                ->orderBy('fecha_certificacion')
                ->get();
            if ($factura_complemento->count() > 0) {
                $last = $factura_complemento->last();
                $consecutivo = $last->folio_interno;
                $series = Serie::get()->pluck('descripcion');
                foreach ($series as $serie)
                    $consecutivo = str_replace($serie, '', $consecutivo);

                $folio = (int)$consecutivo + 1;
            } else if ($is_nomina || $is_nota_credito || $is_complement) {
                $folio = 1;
            } else {
                $folio = 854;
            }
        } else {
            $factura_complemento = $query->where('propietario_id', $propietario)
                ->where('folio_interno', '!=', null)
                ->where('modo_prueba_cfdi', 1)
                ->orderBy('folio_interno')
                ->get();
            if ($factura_complemento->count() > 0) {
                $last = $factura_complemento->last();
                $consecutivo = $last->folio_interno;
                $series = Serie::get()->pluck('descripcion');
                foreach ($series as $serie) {
                    $consecutivo = str_replace($serie, '', $consecutivo);
                }
                $consecutivo = str_replace('-TEST', '', $consecutivo);

                $folio = (int)$consecutivo + 1;
            } else {
                $folio = 1;
            }
            $folio .= '-TEST';
        }
        return $folio;
    }

    public static function get_mes_en($mes)
    {
        $meses = [
            'Ene' => 'Jan',
            'Feb' => 'Feb',
            'Mar' => 'Mar',
            'Abr' => 'Apr',
            'May' => 'May',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Ago' => 'Aug',
            'Sep' => 'Sep',
            'Oct' => 'Oct',
            'Nov' => 'Nov',
            'Dic' => 'Dec'
        ];

        return $meses[$mes];
    }

    public static function getMesEspannol($mes_int)
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

    public static function getMesEspannolSimpl($mes_int)
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

    public static function downloadFile($src, $name)
    {
        header('Content-Type: application/x-download');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo file_get_contents($src);
        exit;
    }

    public static function normalizarDireccionUrl($url){
        $dir_array = explode('/', $url);
        $dir = '';
        for ($i = 1, $iMax = count($dir_array); $i < $iMax; $i++) {
            if ($i !== 1) {
                $dir .= '/' . $dir_array[$i];
            } else {
                $dir = $dir_array[$i];
            }
        }
        return $dir;
    }
}
