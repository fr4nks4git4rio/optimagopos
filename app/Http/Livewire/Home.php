<?php

namespace App\Http\Livewire;

use App\Models\Administracion\CodificadoresGenerales\Operador;
use App\Models\Administracion\CodificadoresGenerales\Unidad;
use App\Models\Administracion\TipoCambio;
use App\Models\User;
use App\Notifications\SiteNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
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
        'ventas_netas' => '',
        'ventas_formas_pago' => [],
        'cantidad_formas_pago' => [],
        'metodo_pago_dominante' => '',
        'grafica_metodos_pago' => [],
        'grafica_comportamiento_pagos' => [],
        'grafica_comportamiento_pagos_hora' => []
    ];

    public $correccionesData = [
        'correcciones' => '',
        'deletes' => '',
        'cancels' => '',
        'influencia_correcciones' => '',
        'grafica_correcciones_operador' => [],
        'grafica_correcciones_hora' => []
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
                $resumen1 = DB::table('tb_tickets as ticket')
                    ->selectRaw("COUNT(ticket.id) as cantidad, SUM(ticket.importe) as total")
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->first();
                $this->resumenData['operaciones'] = $resumen1->cantidad ?? 0;
                $ventas_netas = $resumen1->total ?? 0;
                $importes_devueltos = DB::table('tb_ticket_producto_correcciones as tpc')
                    ->selectRaw("SUM(tpc.precio) as total")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('tpc.nombre', 'Refund')
                    ->value('total') ?? 0;
                $this->resumenData['importes_devueltos'] = abs($importes_devueltos);
                $this->resumenData['ventas_netas'] = $ventas_netas;
                $this->resumenData['ventas_totales'] = DB::table('tb_ticket_productos as tp')
                    ->selectRaw('SUM(tp.precio) as total')
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('tp.precio', '>', 0)
                    ->value('total');
                $this->resumenData['articulos_vendidos'] = DB::table('tb_ticket_productos as tp')
                    ->selectRaw("SUM(ROUND(tp.cantidad)) as cantidad")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->value('cantidad');
                $this->resumenData['multimoneda'] = DB::table('tb_ticket_operaciones as to')
                    ->join('tb_sucursal_forma_pagos as sfp', 'sfp.id', '=', 'to.sucursal_forma_pago_id')
                    ->join('tb_tickets as ticket', 'ticket.id', '=', 'to.ticket_id')
                    ->join('tb_sucursales as sucursal', 'sucursal.id', '=', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->select('to.ticket_id')
                    ->groupBy('to.ticket_id')
                    ->havingRaw('COUNT(DISTINCT sfp.moneda_id) >= 2')
                    ->get()
                    ->count();
                $this->resumenData['ventas_netas_operacion'] = DB::table('tb_tickets as ticket')
                    ->selectRaw("ticket.importe, ticket.id as id, HOUR(ticket.fecha_transaccion) as hora")
                    ->leftJoin('tb_ticket_operaciones as to', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->whereRaw("HOUR(ticket.fecha_transaccion) = ?", [now()->hour])
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->id => $item->importe];
                    })->toArray();
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
                $this->operacionesData['multimoneda'] = DB::table('tb_ticket_operaciones as to')
                    ->join('tb_sucursal_forma_pagos as sfp', 'sfp.id', '=', 'to.sucursal_forma_pago_id')
                    ->join('tb_tickets as ticket', 'ticket.id', '=', 'to.ticket_id')
                    ->join('tb_sucursales as sucursal', 'sucursal.id', '=', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->select('to.ticket_id')
                    ->groupBy('to.ticket_id')
                    ->havingRaw('COUNT(DISTINCT sfp.moneda_id) >= 2')
                    ->get()
                    ->count();
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
                    ->selectRaw("SUM(ROUND(tp.cantidad)) as cantidad")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->value('cantidad');
                $this->productosData['producto_estrella'] = DB::table('tb_ticket_productos as tp')
                    ->select('p.nombre', DB::raw("SUM(ROUND(tp.cantidad)) as cantidad"))
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
                $this->pagosData['ventas_netas'] = DB::table('tb_tickets as ticket')
                    ->selectRaw("SUM(ticket.importe) as total")
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->value('total');
                $this->pagosData['ventas_formas_pago'] = DB::table('tb_ticket_operaciones as to')
                    ->select('sfp.nombre', DB::raw("SUM(ROUND(to.monto, 2)) as total"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->whereNotNull('to.sucursal_forma_pago_id')
                    ->groupBy('sfp.nombre')
                    ->orderByDesc('total')
                    ->get()->pluck('total', 'nombre');
                $this->pagosData['cantidad_formas_pago'] = DB::table('tb_ticket_operaciones as to')
                    ->select('sfp.nombre', DB::raw("COUNT(to.sucursal_forma_pago_id) as cantidad"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->whereNotNull('to.sucursal_forma_pago_id')
                    ->groupBy('sfp.nombre')
                    ->orderByDesc('cantidad')
                    ->get()->pluck('cantidad', 'nombre');
                $this->pagosData['metodo_pago_dominante'] = DB::table('tb_ticket_operaciones as to')
                    ->select('sfp.nombre', DB::raw("COUNT(DISTINCT to.ticket_id) as presencia"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('sfp.nombre')
                    ->orderByDesc('presencia')
                    ->first()->nombre ?? '';
                $totalOperaciones = DB::table('tb_ticket_operaciones as to')
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->whereNotNull('to.sucursal_forma_pago_id')
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
                    ->whereNotNull('to.sucursal_forma_pago_id')
                    ->groupBy('sfp.nombre')
                    ->orderByDesc('porciento')
                    ->get()
                    ->pluck('porciento', 'nombre');
                $this->pagosData['grafica_comportamiento_pagos'] = $graficaFormasPago;
                $this->pagosData['grafica_metodos_pago'] = $graficaFormasPago;
                $datos_comportamiento_pagos_hora = DB::table('tb_ticket_operaciones as to')
                    ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, COUNT(DISTINCT to.id) as cantidad")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->whereNotNull('to.sucursal_forma_pago_id')
                    ->groupBy('hora')
                    ->get()->pluck('cantidad', 'hora');
                $this->pagosData['grafica_comportamiento_pagos_hora'] = $datos_comportamiento_pagos_hora;
                break;
            case 'correcciones':
                // Similar lógica para cargar datos específicos de la sección de correcciones
                $correcciones = DB::table('tb_ticket_producto_correcciones as tpc')
                    ->selectRaw("COUNT(tpc.id) as cantidad, SUM(tpc.precio) as monto")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->get()->first();
                $this->correccionesData['correcciones'] = $correcciones ? ($correcciones->cantidad . ' -> $' . number_format(abs($correcciones->monto), 2)) : '';
                $deletes = DB::table('tb_ticket_producto_correcciones as tpc')
                    ->selectRaw("COUNT(tpc.id) as deletes, SUM(tpc.precio) as monto")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('tpc.nombre', 'Delete')
                    ->get()->first();
                $this->correccionesData['deletes'] = $deletes ? ($deletes->deletes . ' -> $' . number_format(abs($deletes->monto), 2)) : '';
                $cancels = DB::table('tb_ticket_producto_correcciones as tpc')
                    ->selectRaw("COUNT(tpc.id) as cancels, SUM(tpc.precio) as monto")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('tpc.nombre', 'Cancel')
                    ->get()->first();
                $this->correccionesData['cancels'] = $cancels ? ($cancels->cancels . ' -> $' . number_format(abs($cancels->monto), 2)) : '';
                $totalVentas = DB::table('tb_ticket_productos as tp')
                    ->selectRaw("SUM(tp.precio) as total")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->value('total') ?? 0;
                $totalCorrecciones = DB::table('tb_ticket_producto_correcciones as tpc')
                    ->selectRaw("SUM(tpc.precio) as total")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->value('total') ?? 0;
                $this->correccionesData['influencia_correcciones'] = $totalVentas > 0 ? round((abs($totalCorrecciones) / $totalVentas) * 100, 2) : 0;
                $datos_grafica_correcciones_operador = [];
                DB::table('tb_ticket_producto_correcciones as tpc')
                    ->select('empleado.nombre', DB::raw("COUNT(tpc.id) as cantidad"))
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_empleados as empleado', 'empleado.id', 'ticket.empleado_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('empleado.nombre')
                    ->orderByDesc('cantidad')
                    ->get()->map(function ($value) use (&$datos_grafica_correcciones_operador) {
                        $datos_grafica_correcciones_operador[Crypt::decrypt($value->nombre)] = $value->cantidad;
                    });
                $this->correccionesData['grafica_correcciones_operador'] = $datos_grafica_correcciones_operador;
                $datos_grafica_correcciones_hora = [];
                DB::table('tb_ticket_producto_correcciones as tpc')
                    ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, COUNT(tpc.id) as cantidad")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('hora')
                    ->get()->map(function ($value) use (&$datos_grafica_correcciones_hora) {
                        $datos_grafica_correcciones_hora[$value->hora] = $value->cantidad;
                    });
                $this->correccionesData['grafica_correcciones_hora'] = $datos_grafica_correcciones_hora;
                break;
        }
    }
}
