<?php

namespace App\Http\Livewire;

use App\Models\Administracion\CodificadoresGenerales\Operador;
use App\Models\Administracion\CodificadoresGenerales\Unidad;
use App\Models\Administracion\TipoCambio;
use App\Models\Sucursal;
use App\Models\Terminal;
use App\Models\TicketVK;
use App\Models\User;
use App\Notifications\SiteNotification;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Home extends Component
{
    public $tab;
    public $seccion;

    public $fecha_inicio;
    public $fecha_fin;
    public $sucursales_query;
    public $terminales_query;
    public $sucursales = [];
    public $terminales = [];
    public $terminalesDisponibles = [];

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

    public $videoKitchenData = [
        'cantidadOrdenesAbiertas' => '',
        'cantidadOrdenesProcesando' => '',
        'cantidadOrdenesDemoradas' => '',
        'cantidadOrdenesTerminadas' => '',
        'graficaActividad' => [],
        'ordenes' => []
    ];

    public $monedas = [];

    public $queryString = [
        'tab',
        'seccion' => ['except' => null],
        'fecha_inicio' => ['except' => null],
        'fecha_fin' => ['except' => null],
        'sucursales_query' => ['except' => null],
        'terminales_query' => ['except' => null],
    ];

    public function mount()
    {
        $this->tab = in_array($this->tab, ['foh', 'boh']) ? $this->tab : 'foh';
        $this->seccion = $this->seccion ?? 'resumen';
        $this->monedas = DB::table('tb_monedas')->whereNull('deleted_at')->pluck('acronimo', 'id');

        if ($this->sucursales_query) {
            $this->sucursales = explode(',', $this->sucursales_query);
            $this->loadTerminales();
        }
        if ($this->terminales_query)
            $this->terminales = explode(',', $this->terminales_query);
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }
    public function updated($field)
    {
        if ($field == 'sucursales') {
            $this->sucursales_query = implode(',', $this->sucursales);
            $this->loadTerminales();
        }
        if ($field == 'terminales') {
            $this->terminales_query = implode(',', $this->terminales);
        }
        $this->loadData();
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function render()
    {
        if (user()->cliente_id)
            return view('livewire.home', [
                'sucursalesDisponibles' => Sucursal::where('cliente_id', user()->cliente_id)->whereIn('id', user()->sucursales->pluck('id')->toArray())->get()->map(function ($value) {
                    return [
                        'value' => $value->id,
                        'label' => Crypt::decrypt($value->nombre_comercial)
                    ];
                })->toArray()
            ]);
        return view('livewire.home-admin');
    }

    public function init()
    {
        $this->loadData();
    }

    public function loadTerminales()
    {
        $this->terminalesDisponibles = Terminal::whereIn('sucursal_id', Arr::wrap($this->sucursales))->whereIn('id', user()->terminales->pluck('id')->toArray())->get()->map->only(['value', 'label'])->toArray();
    }

    private function commonWhere($query)
    {
        if ($this->fecha_inicio)
            $query->whereDate('ticket.fecha_transaccion', '>=', $this->fecha_inicio);
        if ($this->fecha_fin)
            $query->whereDate('ticket.fecha_transaccion', '<=', $this->fecha_fin);
        if (count(Arr::wrap($this->sucursales)) > 0)
            $query->whereIn('sucursal.id', $this->sucursales);
        else
            $query->whereIn('sucursal.id', user()->sucursales->pluck('id')->toArray());
        if (count(Arr::wrap($this->terminales)) > 0)
            $query->whereIn('terminal.id', $this->terminales);
        else
            $query->whereIn('terminal.id', user()->terminales->pluck('id')->toArray());
        return $query;
    }

    public function loadData($seccion = null)
    {
        switch ($this->tab) {
            case 'foh':
                if ($seccion)
                    $this->seccion = $seccion;

                switch ($this->seccion) {
                    case 'resumen':
                        $resumen1_q = DB::table('tb_tickets as ticket')
                            ->selectRaw("COUNT(ticket.id) as cantidad, SUM(ticket.importe) as total")
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $resumen1_q = $this->commonWhere($resumen1_q);
                        $resumen1 = $resumen1_q->first();
                        $this->resumenData['operaciones'] = $resumen1->cantidad ?? 0;
                        $ventas_netas = $resumen1->total ?? 0;

                        $importes_devueltos_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->selectRaw("SUM(tpc.precio) as total")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->where('tpc.nombre', 'Refund');

                        $importes_devueltos_q = $this->commonWhere($importes_devueltos_q);
                        $importes_devueltos = $importes_devueltos_q->value('total') ?? 0;
                        $this->resumenData['importes_devueltos'] = abs($importes_devueltos);
                        $this->resumenData['ventas_netas'] = $ventas_netas;

                        $ventas_totales_q = DB::table('tb_ticket_productos as tp')
                            ->selectRaw('SUM(tp.precio) as total')
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->where('tp.precio', '>', 0);

                        $ventas_totales_q = $this->commonWhere($ventas_totales_q);
                        $this->resumenData['ventas_totales'] = $ventas_totales_q->value('total');

                        $articulos_vendidos_q = DB::table('tb_ticket_productos as tp')
                            ->selectRaw("SUM(ROUND(tp.cantidad)) as cantidad")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->join('tb_terminales as terminal', 'terminal.id', '=', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $articulos_vendidos_q = $this->commonWhere($articulos_vendidos_q);
                        $this->resumenData['articulos_vendidos'] = $articulos_vendidos_q->value('cantidad');

                        $multimoneda_q = DB::table('tb_ticket_operaciones as to')
                            ->join('tb_sucursal_forma_pagos as sfp', 'sfp.id', '=', 'to.sucursal_forma_pago_id')
                            ->join('tb_tickets as ticket', 'ticket.id', '=', 'to.ticket_id')
                            ->join('tb_sucursales as sucursal', 'sucursal.id', '=', 'ticket.sucursal_id')
                            ->join('tb_terminales as terminal', 'terminal.id', '=', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->select('to.ticket_id')
                            ->groupBy('to.ticket_id')
                            ->havingRaw('COUNT(DISTINCT sfp.moneda_id) >= 2');

                        $multimoneda_q = $this->commonWhere($multimoneda_q);
                        $this->resumenData['multimoneda'] = $multimoneda_q->count();

                        $ventas_neta_operacion_q = DB::table('tb_tickets as ticket')
                            ->selectRaw("ticket.importe, ticket.id as id")
                            ->leftJoin('tb_ticket_operaciones as to', 'ticket.id', 'to.ticket_id')
                            ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->orderByRaw("HOUR(ticket.fecha_transaccion) asc")
                            ->where('ticket.importe', '>', 0);

                        $ventas_neta_operacion_q = $this->commonWhere($ventas_neta_operacion_q);
                        $this->resumenData['ventas_netas_operacion'] = $ventas_neta_operacion_q->take(15)
                            ->pluck('importe');

                        $correcciones_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->selectRaw("SUM(IF(tpc.nombre = 'Delete', 1, 0)) as deletes, SUM(IF(tpc.nombre = 'Cancel', 1, 0)) as cancels")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $correcciones_q = $this->commonWhere($correcciones_q);
                        $correcciones = $correcciones_q->first();
                        $this->resumenData['deletes'] = $correcciones->deletes ?? 0;
                        $this->resumenData['cancels'] = $correcciones->cancels ?? 0;

                        $datos_grafica_actividad = [];
                        $datos_grafica_actividad_q = DB::table('tb_tickets as ticket')
                            ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, COUNT(ticket.id) as cantidad")
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('hora');

                        $datos_grafica_actividad_q = $this->commonWhere($datos_grafica_actividad_q);
                        $datos_grafica_actividad_q->get()->map(function ($value, $key) use (&$datos_grafica_actividad) {
                            $datos_grafica_actividad[$value->hora] = $value->cantidad;
                        });
                        $this->resumenData['grafica_actividad'] = $datos_grafica_actividad;

                        $ultimo_ticket_q = DB::table('tb_tickets as ticket')
                            ->select('ticket.*', 'terminal.id_pos', 'empleado.nombre as empleado')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->leftJoin('tb_empleados as empleado', 'empleado.id', 'ticket.empleado_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $ultimo_ticket_q =  $this->commonWhere($ultimo_ticket_q);
                        $this->resumenData['ultimo_ticket'] = (array) $ultimo_ticket_q->latest()
                            ->first();
                        break;
                    case 'operaciones':
                        // Similar lógica para cargar datos específicos de la sección de operaciones
                        $operaciones_q = DB::table('tb_ticket_operaciones as to')
                            ->select('to.*')
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $operaciones_q = $this->commonWhere($operaciones_q);
                        $this->operacionesData['operaciones'] = $operaciones_q->count();

                        $tickets_promedio_q = DB::table('tb_tickets as ticket')
                            ->selectRaw("AVG(ticket.importe) as promedio")
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->where('ticket.importe', '>', 0);

                        $tickets_promedio_q = $this->commonWhere($tickets_promedio_q);
                        $this->operacionesData['ticket_promedio'] = $tickets_promedio_q->value('promedio');

                        $mayor_ticket_q = DB::table('tb_tickets as ticket')
                            ->selectRaw("MAX(ticket.importe) as mayor")
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->where('ticket.importe', '>', 0);

                        $mayor_ticket_q = $this->commonWhere($mayor_ticket_q);
                        $this->operacionesData['mayor_ticket'] = $mayor_ticket_q->value('mayor');

                        $menor_ticket_q = DB::table('tb_tickets as ticket')
                            ->selectRaw("MIN(ticket.importe) as menor")
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->where('ticket.importe', '>', 0);

                        $menor_ticket_q = $this->commonWhere($menor_ticket_q);
                        $this->operacionesData['menor_ticket'] = $menor_ticket_q->value('menor');

                        $correcciones_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->selectRaw("COUNT(tpc.id) as cantidad")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $correcciones_q = $this->commonWhere($correcciones_q);
                        $this->operacionesData['correcciones'] = $correcciones_q->first()->cantidad ?? 0;

                        $multimoneda_q = DB::table('tb_ticket_operaciones as to')
                            ->join('tb_sucursal_forma_pagos as sfp', 'sfp.id', '=', 'to.sucursal_forma_pago_id')
                            ->join('tb_tickets as ticket', 'ticket.id', '=', 'to.ticket_id')
                            ->join('tb_sucursales as sucursal', 'sucursal.id', '=', 'ticket.sucursal_id')
                            ->join('tb_terminales as terminal', 'terminal.id', '=', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->select('to.ticket_id')
                            ->groupBy('to.ticket_id')
                            ->havingRaw('COUNT(DISTINCT sfp.moneda_id) >= 2');

                        $multimoneda_q = $this->commonWhere($multimoneda_q);
                        $this->operacionesData['multimoneda'] = $multimoneda_q->count();

                        $datos_grafica_ventas_hora = [];
                        $datos_grafica_operaciones_hora = [];
                        $resultado_q = DB::table('tb_tickets as ticket')
                            ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, SUM(ticket.importe) as total, COUNT(ticket.id) as cantidad")
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('hora');

                        $resultado_q = $this->commonWhere($resultado_q);
                        $resultado_q->get()->map(function ($value, $key) use (&$datos_grafica_ventas_hora, &$datos_grafica_operaciones_hora) {
                            $datos_grafica_ventas_hora[$value->hora] = $value->total;
                            $datos_grafica_operaciones_hora[$value->hora] = $value->cantidad;
                        });
                        $this->operacionesData['grafica_ventas_hora'] = $datos_grafica_ventas_hora;
                        $this->operacionesData['grafica_operaciones_hora'] = $datos_grafica_operaciones_hora;

                        $top_tickets_q = DB::table('tb_tickets as ticket')
                            ->select('ticket.id', 'ticket.id_transaccion', 'ticket.importe')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->orderByDesc('ticket.importe')
                            ->limit(5);

                        $top_tickets_q = $this->commonWhere($top_tickets_q);
                        $this->operacionesData['top_tickets'] = $top_tickets_q->get();
                        break;
                    case 'productos':
                        // Similar lógica para cargar datos específicos de la sección de productos
                        $articulos_vencidos_q = DB::table('tb_ticket_productos as tp')
                            ->selectRaw("SUM(ROUND(tp.cantidad)) as cantidad")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $articulos_vencidos_q = $this->commonWhere($articulos_vencidos_q);
                        $this->productosData['articulos_vendidos'] = $articulos_vencidos_q->value('cantidad');

                        $producto_estrella_q = DB::table('tb_ticket_productos as tp')
                            ->select('p.nombre', DB::raw("SUM(ROUND(tp.cantidad)) as cantidad"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('p.nombre')
                            ->orderByDesc('cantidad');

                        $producto_estrella_q = $this->commonWhere($producto_estrella_q);
                        $this->productosData['producto_estrella'] = $producto_estrella_q->first()->nombre ?? '';

                        $mas_popular_q = DB::table('tb_ticket_productos as tp')
                            ->select('p.nombre', DB::raw("COUNT(DISTINCT tp.ticket_id) as presencia"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('p.nombre')
                            ->orderByDesc('presencia');

                        $mas_popular_q = $this->commonWhere($mas_popular_q);
                        $this->productosData['mas_popular'] = $mas_popular_q->first()->nombre ?? '';

                        $mayor_ingreso_q = DB::table('tb_ticket_productos as tp')
                            ->select('p.nombre', DB::raw("SUM(tp.precio) as ingreso"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('p.nombre')
                            ->orderByDesc('ingreso');

                        $mayor_ingreso_q = $this->commonWhere($mayor_ingreso_q);
                        $this->productosData['mayor_ingreso'] = $mayor_ingreso_q->first()->nombre ?? '';

                        $top_productos_cantidad_q = DB::table('tb_ticket_productos as tp')
                            ->select('p.nombre', DB::raw("SUM(tp.cantidad) as cantidad"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('p.nombre')
                            ->orderByDesc('cantidad');

                        $top_productos_cantidad_q = $this->commonWhere($top_productos_cantidad_q);
                        $this->productosData['top_productos_cantidad'] = $top_productos_cantidad_q->limit(5)
                            ->pluck('cantidad', 'nombre');

                        $top_productos_ingreso_q = DB::table('tb_ticket_productos as tp')
                            ->select('p.nombre', DB::raw("ROUND(SUM(tp.precio), 2) as ingreso"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_productos as p', 'p.id', 'tp.producto_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('p.nombre')
                            ->orderByDesc('ingreso');

                        $top_productos_ingreso_q = $this->commonWhere($top_productos_ingreso_q);
                        $this->productosData['top_productos_ingreso'] = $top_productos_ingreso_q->limit(5)
                            ->get()->pluck('ingreso', 'nombre');
                        break;
                    case 'pagos':
                        $ventas_netas_q = DB::table('tb_tickets as ticket')
                            ->selectRaw("SUM(ticket.importe) as total")
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $ventas_netas_q = $this->commonWhere($ventas_netas_q);
                        $this->pagosData['ventas_netas'] = $ventas_netas_q->value('total');

                        $ventas_formas_pago_q = DB::table('tb_ticket_operaciones as to')
                            ->select('sfp.nombre', DB::raw("SUM(ROUND(to.monto, 2)) as total"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                            ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->whereNotNull('to.sucursal_forma_pago_id')
                            ->groupBy('sfp.nombre')
                            ->orderByDesc('total');

                        $ventas_formas_pago_q = $this->commonWhere($ventas_formas_pago_q);
                        $this->pagosData['ventas_formas_pago'] = $ventas_formas_pago_q->pluck('total', 'nombre');

                        $cantidad_formas_pago = DB::table('tb_ticket_operaciones as to')
                            ->select('sfp.nombre', DB::raw("COUNT(to.sucursal_forma_pago_id) as cantidad"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                            ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->whereNotNull('to.sucursal_forma_pago_id')
                            ->groupBy('sfp.nombre')
                            ->orderByDesc('cantidad');

                        $cantidad_formas_pago = $this->commonWhere($cantidad_formas_pago);
                        $this->pagosData['cantidad_formas_pago'] = $cantidad_formas_pago->pluck('cantidad', 'nombre');

                        $metodo_pago_dominante_q = DB::table('tb_ticket_operaciones as to')
                            ->select('sfp.nombre', DB::raw("COUNT(DISTINCT to.ticket_id) as presencia"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                            ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('sfp.nombre')
                            ->orderByDesc('presencia');

                        $metodo_pago_dominante_q = $this->commonWhere($metodo_pago_dominante_q);
                        $this->pagosData['metodo_pago_dominante'] = $metodo_pago_dominante_q->first()->nombre ?? '';

                        $total_operaciones_q = DB::table('tb_ticket_operaciones as to')
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->whereNotNull('to.sucursal_forma_pago_id');

                        $total_operaciones_q = $this->commonWhere($total_operaciones_q);
                        $totalOperaciones = $total_operaciones_q->count();

                        // Evitamos división por cero si el salón es nuevo y no tiene operaciones aún
                        $totalOperaciones = $totalOperaciones > 0 ? $totalOperaciones : 1;

                        // 2. Calculamos los porcentajes por forma de pago
                        $graficas_forma_pago_q = DB::table('tb_ticket_operaciones as to')
                            ->select(
                                'sfp.nombre',
                                DB::raw("ROUND((COUNT(to.id) * 100.0 / {$totalOperaciones}), 2) as porciento")
                            )
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                            ->join('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->whereNotNull('to.sucursal_forma_pago_id')
                            ->groupBy('sfp.nombre')
                            ->orderByDesc('porciento');

                        $graficas_forma_pago_q = $this->commonWhere($graficas_forma_pago_q);
                        $graficaFormasPago = $graficas_forma_pago_q->pluck('porciento', 'nombre');
                        $this->pagosData['grafica_comportamiento_pagos'] = $graficaFormasPago;
                        $this->pagosData['grafica_metodos_pago'] = $graficaFormasPago;

                        $datos_comportamiento_pagos_hora_q = DB::table('tb_ticket_operaciones as to')
                            ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, COUNT(DISTINCT to.id) as cantidad")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->whereNotNull('to.sucursal_forma_pago_id')
                            ->groupBy('hora');

                        $datos_comportamiento_pagos_hora_q = $this->commonWhere($datos_comportamiento_pagos_hora_q);
                        $datos_comportamiento_pagos_hora = $datos_comportamiento_pagos_hora_q->pluck('cantidad', 'hora');
                        $this->pagosData['grafica_comportamiento_pagos_hora'] = $datos_comportamiento_pagos_hora;
                        break;
                    case 'correcciones':
                        // Similar lógica para cargar datos específicos de la sección de correcciones
                        $correcciones_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->selectRaw("COUNT(tpc.id) as cantidad, SUM(tpc.precio) as monto")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $correcciones_q = $this->commonWhere($correcciones_q);
                        $correcciones = $correcciones_q->first();
                        $this->correccionesData['correcciones'] = $correcciones ? ($correcciones->cantidad . ' -> $' . number_format(abs($correcciones->monto), 2)) : '';

                        $deletes_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->selectRaw("COUNT(tpc.id) as deletes, SUM(tpc.precio) as monto")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->where('tpc.nombre', 'Delete');

                        $deletes_q = $this->commonWhere($deletes_q);
                        $deletes = $deletes_q->first();
                        $this->correccionesData['deletes'] = $deletes ? ($deletes->deletes . ' -> $' . number_format(abs($deletes->monto), 2)) : '';

                        $cancels_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->selectRaw("COUNT(tpc.id) as cancels, SUM(tpc.precio) as monto")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->where('tpc.nombre', 'Cancel');

                        $cancels_q = $this->commonWhere($cancels_q);
                        $cancels = $cancels_q->first();
                        $this->correccionesData['cancels'] = $cancels ? ($cancels->cancels . ' -> $' . number_format(abs($cancels->monto), 2)) : '';

                        $total_ventas_q = DB::table('tb_ticket_productos as tp')
                            ->selectRaw("SUM(tp.precio) as total")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $total_ventas_q = $this->commonWhere($total_ventas_q);
                        $totalVentas = $total_ventas_q->value('total') ?? 0;

                        $total_correcciones_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->selectRaw("SUM(tpc.precio) as total")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $total_correcciones_q = $this->commonWhere($total_correcciones_q);
                        $totalCorrecciones = $total_correcciones_q->value('total') ?? 0;
                        $this->correccionesData['influencia_correcciones'] = $totalVentas > 0 ? round((abs($totalCorrecciones) / $totalVentas) * 100, 2) : 0;

                        $datos_grafica_correcciones_operador = [];
                        $grafica_correcciones_operador_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->select('empleado.nombre', DB::raw("COUNT(tpc.id) as cantidad"))
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_empleados as empleado', 'empleado.id', 'ticket.empleado_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id)
                            ->groupBy('empleado.nombre')
                            ->orderByDesc('cantidad');

                        $grafica_correcciones_operador_q = $this->commonWhere($grafica_correcciones_operador_q);
                        $grafica_correcciones_operador_q->get()->map(function ($value) use (&$datos_grafica_correcciones_operador) {
                            $datos_grafica_correcciones_operador[Crypt::decrypt($value->nombre)] = $value->cantidad;
                        });
                        $this->correccionesData['grafica_correcciones_operador'] = $datos_grafica_correcciones_operador;

                        $datos_grafica_correcciones_hora = [];
                        $grafica_correcciones_hora_q = DB::table('tb_ticket_producto_correcciones as tpc')
                            ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, COUNT(tpc.id) as cantidad")
                            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tpc.ticket_id')
                            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                            ->where('sucursal.cliente_id', user()->cliente_id);

                        $grafica_correcciones_hora_q = $this->commonWhere($grafica_correcciones_hora_q);
                        $grafica_correcciones_hora_q->groupBy('hora')
                            ->get()->map(function ($value) use (&$datos_grafica_correcciones_hora) {
                                $datos_grafica_correcciones_hora[$value->hora] = $value->cantidad;
                            });
                        $this->correccionesData['grafica_correcciones_hora'] = $datos_grafica_correcciones_hora;
                        break;
                }
                break;
            case 'boh':
                $cantidadOrdenesAbiertas_q = DB::table('tb_tickets_vk as ticket')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('ticket.estado', 1);

                $this->videoKitchenData['cantidadOrdenesAbiertas'] = $this->commonWhere($cantidadOrdenesAbiertas_q)->count();

                $cantidadOrdenesEnProceso_q = DB::table('tb_tickets_vk as ticket')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('ticket.estado', 2);

                $this->videoKitchenData['cantidadOrdenesProcesando'] = $this->commonWhere($cantidadOrdenesEnProceso_q)->count();

                $cantidadOrdenesDemoradas_q = DB::table('tb_tickets_vk as ticket')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('ticket.estado', 4);

                $this->videoKitchenData['cantidadOrdenesDemoradas'] = $this->commonWhere($cantidadOrdenesDemoradas_q)->count();

                $cantidadOrdenesTerminadas_q = DB::table('tb_tickets_vk as ticket')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->where('ticket.estado', 3);

                $this->videoKitchenData['cantidadOrdenesTerminadas'] = $this->commonWhere($cantidadOrdenesTerminadas_q)->count();

                $datos_grafica_actividad = [];
                $datos_grafica_actividad_q = DB::table('tb_tickets_vk as ticket')
                    ->selectRaw("HOUR(ticket.fecha_transaccion) as hora, COUNT(ticket.id) as cantidad")
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('hora');

                $datos_grafica_actividad_q = $this->commonWhere($datos_grafica_actividad_q);
                $datos_grafica_actividad_q->get()->map(function ($value, $key) use (&$datos_grafica_actividad) {
                    $datos_grafica_actividad[$value->hora] = $value->cantidad;
                });
                $this->videoKitchenData['graficaActividad'] = $datos_grafica_actividad;

                $ordenes_q =  DB::table('tb_tickets_vk as ticket')
                    ->select(
                        'ticket.id as id',
                        'ticket.id_transaccion',
                        'ticket.mesa',
                        'ticket.asiento',
                        'ticket.fecha_transaccion',
                        'ticket.estado',
                        'ticket.terminal_id',
                        'depa.nombre as departamento',
                        'sucursal.id as sucursal_id',
                        DB::raw("CONCAT_WS(' - ',terminal.nombre, terminal.identificador) as terminal"),
                        'sucursal.nombre_comercial as sucursal',
                    )
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id')
                    ->leftJoin('tb_departamentos as depa', 'depa.id', 'ticket.departamento_id')
                    ->where('sucursal.cliente_id', user()->cliente_id);

                $groups = [];
                $this->commonWhere($ordenes_q)
                    ->orderBy('ticket.fecha_transaccion')
                    ->get()->each(function ($element) use (&$groups) {
                        $element->sucursal = Crypt::decrypt($element->sucursal);
                        $element->fecha_transaccion = SupportCarbon::parse($element->fecha_transaccion)->format('d M Y H:i:s');
                        if (!key_exists($element->sucursal_id,  $groups)) {
                            $groups[$element->sucursal_id]['label'] = $element->sucursal;
                        }
                        $groups[$element->sucursal_id]['data'][] = (array)$element;
                    });
                $this->videoKitchenData['ordenes'] = $groups;
                break;
        }
    }
}
