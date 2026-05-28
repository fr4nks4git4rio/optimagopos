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
        'ventas_netas' => [],
        'ventas_netas_operacion' => [],
        'ventas_totales' => [],
        'importes_devueltos' => [],
        'deletes' => '',
        'cancels' => '',
        'multimoneda' => '',
        'articulos_vendidos' => '',
        'cuarentena' => '',
        'grafica_actividad' => [],
        'ultimo_ticket' => null
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
                $ventas_netas = [];
                $ventas_totales = [];
                $importes_devueltos = [];
                $ventas_netas_operacion = [];

                $operaciones = DB::table('tb_ticket_operaciones as to')
                    ->select('to.*', 'sfp.moneda_id')
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'to.sucursal_forma_pago_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->groupBy('to.id')
                    ->get()->map(function ($element) use (&$ventas_totales, &$ventas_netas, &$ventas_netas_operacion, &$importes_devueltos) {
                        if (!$element->es_cambio) {
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

                            if ($monto > 0) {
                                if (isset($ventas_netas_operacion[$element->moneda_id])) {
                                    $ventas_netas_operacion[$element->moneda_id]['montos'][] = $monto;
                                } else {
                                    $ventas_netas_operacion[$element->moneda_id]['moneda'] = $this->monedas[$element->moneda_id];
                                    $ventas_netas_operacion[$element->moneda_id]['montos'][] = $monto;
                                }
                            }

                            if (isset($importes_devueltos[$element->moneda_id])) {
                                $importes_devueltos[$element->moneda_id]['monto'] += $monto < 0 ? abs($monto) : 0;
                            } else {
                                $importes_devueltos[$element->moneda_id]['moneda'] = $this->monedas[$element->moneda_id];
                                $importes_devueltos[$element->moneda_id]['monto'] = $monto < 0 ? abs($monto) : 0;
                            }
                        }
                    });

                $this->resumenData['operaciones'] = count($operaciones);
                $this->resumenData['ventas_totales'] = $ventas_totales;
                $this->resumenData['ventas_netas'] = $ventas_netas;
                $this->resumenData['ventas_netas_operacion'] = $ventas_netas_operacion;
                $this->resumenData['importes_devueltos'] = $importes_devueltos;
                $this->resumenData['articulos_vendidos'] = DB::table('tb_ticket_productos as tp')
                    ->selectRaw("SUM(tp.cantidad) as cantidad")
                    ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->value('cantidad');
                $this->resumenData['multimoneda'] = DB::table('tb_tickets as ticket')
                    ->selectRaw("COUNT(DISTINCT ticket.id) as cantidad, COUNT(DISTINCT to.sucursal_forma_pago_id) as cant_fps")
                    ->leftJoin('tb_ticket_operaciones as to', 'ticket.id', 'to.ticket_id')
                    ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
                    ->where('sucursal.cliente_id', user()->cliente_id)
                    ->having('cant_fps', '>', 1)
                    ->value('cantidad');
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
        }
    }
}
