<?php

namespace App\Http\Livewire;

use App\Models\Administracion\CodificadoresGenerales\Operador;
use App\Models\Administracion\CodificadoresGenerales\Unidad;
use App\Models\Administracion\TipoCambio;
use App\Models\User;
use App\Notifications\SiteNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Home extends Component
{
    public $seccion;

    public $resumenData = [
        'operaciones' => '',
        'ventas_netas' => '',
        'ventas_netas_operacion' => [],
        'ventas_totales' => '',
        'importes_devueltos' => '',
        'deletes' => '',
        'cancels' => '',
        'multimoneda' => '',
        'articulos_vendidos' => '',
        'cuarentena' => '',
        'grafica_actividad' => [],
        'ultimo_ticket' => null
    ];

    public $operacionesData = [
        'operaciones' => '',
        'ticket_promedio' => '',
        'mayor_ticket' => '',
        'menor_ticket' => '',
        'correcciones' => '',
        'multimoneda' => '',
        'grafica_ventas_hora' => [],
        'grafica_operaciones_hora' => [],
        'top_tickets' => []
    ];

    public $productosData = [
        'articulos_vendidos' => '',
        'producto_estrella' => '',
        'mas_popular' => '',
        'mayor_ingreso' => '',
        'top_productos_cantidad' => [],
        'top_productos_ingreso' => []
    ];

    public $pagosData = [
        'ventas_netas' => [],
        'ventas_totales' => [],
        'metodo_pago_dominante' => '',
        'grafica_metodos_pago' => [],
        'grafica_comportamiento_pagos' => []
    ];

    public $monedas = [];

    public $queryString = [
        'seccion' => ['except' => null]
    ];

    public function mount()
    {
        $this->seccion = $this->seccion ?? 'resumen';
        $this->monedas = DB::table('tb_monedas')->pluck('acronimo', 'id');
    }
    public function render()
    {
        return view('livewire.home');
    }

    public function init()
    {
        $this->loadData();
    }

    public function loadData($seccion = null)
    {
        if ($seccion)
            $this->seccion = $seccion;

        switch ($this->seccion) {
            case 'resumen':
                $ventas_netas = 0;
                $ventas_totales = 0;
                $importes_devueltos = 0;
                $ventas_netas_operacion = [];

                DB::table('tb_ticket_productos as tp')
                    ->select('tp.*', 'ticket.id as ticket_id')
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('tp.id')
                    ->get()->map(function ($element) use (&$ventas_totales, &$ventas_netas, &$ventas_netas_operacion, &$importes_devueltos) {
                        $precio = (float)$element->precio;

                        $ventas_totales += $precio > 0 ? $precio : 0;
                        $ventas_netas += $precio;
                        $importes_devueltos += $precio < 0 ? abs($precio) : 0;

                        if (isset($ventas_netas_operacion[$element->ticket_id])) {
                            $ventas_netas_operacion[$element->ticket_id] += round($precio, 2);
                        } else {
                            $ventas_netas_operacion[$element->ticket_id] = round($precio, 2);
                        }
                    });

                $this->resumenData['operaciones'] = DB::table('tb_tickets as ticket')
                    ->select('ticket.*')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->count();
                $this->resumenData['ventas_totales'] = $ventas_totales;
                $this->resumenData['ventas_netas'] = $ventas_netas;
                $this->resumenData['importes_devueltos'] = $importes_devueltos;
                $this->resumenData['articulos_vendidos'] = DB::table('tb_ticket_productos as tp')
                    ->selectRaw("SUM(tp.cantidad) as cantidad")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->value('cantidad');
                $this->resumenData['multimoneda'] = DB::table('tb_tickets as ticket')
                    ->selectRaw("COUNT(DISTINCT to.ticket_id) as cantidad, COUNT(DISTINCT sfp.moneda_id) as cant_monedas")
                    ->leftJoin('tb_ticket_operaciones as to', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->having('cant_monedas', '>', 1)
                    ->value('cantidad');
                $this->resumenData['ventas_netas_operacion'] = $ventas_netas_operacion;
                $correcciones = DB::table('tb_ticket_producto_correcciones as tpc')
                    ->selectRaw("SUM(IF(tpc.nombre = 'Delete', 1, 0)) as deletes, SUM(IF(tpc.nombre = 'Cancel', 1, 0)) as cancels")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->get()->first();
                $this->resumenData['deletes'] = $correcciones->deletes ?? 0;
                $this->resumenData['cancels'] = $correcciones->cancels ?? 0;
                $datos_grafica_actividad = [];
                DB::table('tb_tickets as ticket')
                    ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, COUNT(ticket.id) as cantidad")
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('hora')
                    ->get()->map(function ($value, $key) use (&$datos_grafica_actividad) {
                        $datos_grafica_actividad[$value->hora] = $value->cantidad;
                    });
                $this->resumenData['grafica_actividad'] = $datos_grafica_actividad;
                $this->resumenData['ultimo_ticket'] = DB::table('tb_tickets as ticket')
                    ->select('ticket.*', 'terminal.id_pos', 'empleado.nombre as empleado')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                    ->leftJoin('tb_empleados as empleado', 'empleado.id', 'ticket.empleado_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->latest()
                    ->first();
                break;
            case 'operaciones':
                // Similar lógica para cargar datos específicos de la sección de operaciones
                $this->operacionesData['operaciones'] = DB::table('tb_ticket_operaciones as to')
                    ->select('to.*')
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->count();
                $this->operacionesData['ticket_promedio'] = DB::table('tb_tickets as ticket')
                    ->selectRaw("AVG(ticket.importe) as promedio")
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('ticket.importe', '>', 0)
                    ->value('promedio');
                $this->operacionesData['mayor_ticket'] = DB::table('tb_tickets as ticket')
                    ->selectRaw("MAX(ticket.importe) as mayor")
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('ticket.importe', '>', 0)
                    ->value('mayor');
                $this->operacionesData['menor_ticket'] = DB::table('tb_tickets as ticket')
                    ->selectRaw("MIN(ticket.importe) as menor")
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('ticket.importe', '>', 0)
                    ->value('menor');
                $this->operacionesData['correcciones'] = DB::table('tb_ticket_producto_correcciones as tpc')
                    ->selectRaw("COUNT(tpc.id) as cantidad")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->get()->first()->cantidad ?? 0;
                $this->operacionesData['multimoneda'] = DB::table('tb_tickets as ticket')
                    ->selectRaw("COUNT(DISTINCT to.ticket_id) as cantidad, COUNT(DISTINCT sfp.moneda_id) as cant_monedas")
                    ->leftJoin('tb_ticket_operaciones as to', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->having('cant_monedas', '>', 1)
                    ->value('cantidad');
                $datos_grafica_ventas_hora = [];
                $datos_grafica_operaciones_hora = [];
                DB::table('tb_tickets as ticket')
                    ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, SUM(ticket.importe) as total, COUNT(ticket.id) as cantidad")
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('hora')
                    ->get()->map(function ($value, $key) use (&$datos_grafica_ventas_hora, &$datos_grafica_operaciones_hora) {
                        $datos_grafica_ventas_hora[$value->hora] = $value->total;
                        $datos_grafica_operaciones_hora[$value->hora] = $value->cantidad;
                    });
                $this->operacionesData['grafica_ventas_hora'] = $datos_grafica_ventas_hora;
                $this->operacionesData['grafica_operaciones_hora'] = $datos_grafica_operaciones_hora;
                $this->operacionesData['top_tickets'] = DB::table('tb_tickets as ticket')
                    ->select('ticket.id', 'ticket.id_transaccion', 'ticket.importe')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->orderByDesc('ticket.importe')
                    ->limit(5)
                    ->get();
                break;
            case 'productos':
                // Similar lógica para cargar datos específicos de la sección de productos
                $this->productosData['articulos_vendidos'] = DB::table('tb_ticket_productos as tp')
                    ->selectRaw("SUM(tp.cantidad) as cantidad")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->value('cantidad');
                $this->productosData['producto_estrella'] = DB::table('tb_ticket_productos as tp')
                    ->select('p.nombre', DB::raw("SUM(tp.cantidad) as cantidad"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('p.nombre')
                    ->orderByDesc('cantidad')
                    ->first()->nombre ?? '';
                $this->productosData['mas_popular'] = DB::table('tb_ticket_productos as tp')
                    ->select('p.nombre', DB::raw("COUNT(DISTINCT tp.ticket_id) as presencia"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('p.nombre')
                    ->orderByDesc('presencia')
                    ->first()->nombre ?? '';
                $this->productosData['mayor_ingreso'] = DB::table('tb_ticket_productos as tp')
                    ->select('p.nombre', DB::raw("SUM(tp.precio) as ingreso"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('p.nombre')
                    ->orderByDesc('ingreso')
                    ->first()->nombre ?? '';
                $this->productosData['top_productos_cantidad'] = DB::table('tb_ticket_productos as tp')
                    ->select('p.nombre', DB::raw("SUM(tp.cantidad) as cantidad"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('p.nombre')
                    ->orderByDesc('cantidad')
                    ->limit(5)
                    ->get()->pluck('cantidad', 'nombre');
                $this->productosData['top_productos_ingreso'] = DB::table('tb_ticket_productos as tp')
                    ->select('p.nombre', DB::raw("ROUND(SUM(tp.precio), 2) as ingreso"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('p.nombre')
                    ->orderByDesc('ingreso')
                    ->limit(5)
                    ->get()->pluck('ingreso', 'nombre');
                break;
            case 'pagos':
                // Similar lógica para cargar datos específicos de la sección de pagos
                $ventas_netas = [];
                $ventas_totales = [];

                $operaciones = DB::table('tb_ticket_operaciones as to')
                    ->select('to.*', 'sfp.moneda_id', 'sfp.forma_pago_id')
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('to.id')
                    ->get()->map(function ($element) use (&$ventas_totales, &$ventas_netas) {
                        if (!$element->es_cambio && $element->moneda_id) {
                            $monto = (float)$element->monto;

                            if (isset($ventas_totales[$element->moneda_id])) {
                                $ventas_totales[$element->moneda_id]['monto'] += $monto > 0 ? $monto : 0;
                            } else {
                                $ventas_totales[$element->moneda_id]['moneda'] = $this->monedas[$element->moneda_id];
                                $ventas_totales[$element->moneda_id]['monto'] = $monto > 0 ? $monto : 0;
                            }

                            if (isset($ventas_netas[$element->moneda_id])) {
                                $ventas_netas[$element->moneda_id]['monto'] += $monto;
                            } else {
                                $ventas_netas[$element->moneda_id]['moneda'] = $this->monedas[$element->moneda_id];
                                $ventas_netas[$element->moneda_id]['monto'] = $monto;
                            }

                            // Lógica para determinar método de pago dominante y datos de gráficas...
                        }
                    });

                // Asignar resultados a propiedades públicas
                $this->pagosData['ventas_netas'] = $ventas_netas;
                $this->pagosData['ventas_totales'] = $ventas_totales;
                $this->pagosData['metodo_pago_dominante'] = DB::table('tb_ticket_operaciones as to')
                    ->select('sfp.nombre', DB::raw("COUNT(DISTINCT to.ticket_id) as presencia"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('sfp.nombre')
                    ->orderByDesc('presencia')
                    ->first()->nombre ?? '';
                $this->pagosData['grafica_metodos_pago'] = DB::table('tb_ticket_operaciones as to')
                    ->select('sfp.nombre', DB::raw("COUNT(to.sucursal_forma_pago_id) as cantidad"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->whereNotNull('to.sucursal_forma_pago_id')
                    ->groupBy('sfp.nombre')
                    ->orderByDesc('cantidad')
                    ->get()->pluck('cantidad', 'nombre'); // Cargar datos para gráfica de métodos de pago
                // 1. Primero obtenemos el total general de operaciones del cliente para usarlo como base
                $totalOperaciones = DB::table('tb_ticket_operaciones as to')
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->count();

                // Evitamos división por cero si el salón es nuevo y no tiene operaciones aún
                $totalOperaciones = $totalOperaciones > 0 ? $totalOperaciones : 1;

                // 2. Calculamos los porcentajes por forma de pago
                $graficaFormasPago = DB::table('tb_ticket_operaciones as to')
                    ->select(
                        'sfp.nombre',
                        DB::raw("ROUND((COUNT(to.id) * 100.0 / {$totalOperaciones}), 2) as porciento")
                    )
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->join('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('sfp.nombre')
                    ->orderByDesc('porciento')
                    ->get()
                    ->pluck('porciento', 'nombre');
                $this->pagosData['grafica_comportamiento_pagos'] = $graficaFormasPago;
                break;
        }
    }
}
