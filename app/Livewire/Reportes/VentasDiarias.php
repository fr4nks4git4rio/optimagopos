<?php

namespace App\Livewire\Reportes;

use App\Exports\FacturaEmitidaExport;
use App\Exports\VentasDiariasExport;
use App\Http\Libraries\Pdf;
use App\Models\Facturador;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class VentasDiarias extends Component
{
    public $perPages;
    public $order;
    public $sort = 'Fecha';
    public $sorts;
    public $fechaInicio;
    public $fechaFin;
    public $sucursal = [];
    public $iframeContainerClass = '';
    public $iframeSrc = '';
    //    public $filter = 'Activos';
    //    public $filters;

    protected $queryString = [
        'order' => ['except' => null],
        'sort' => ['except' => null],
        'fechaInicio' => ['except' => null],
        'fechaFin' => ['except' => null]
    ];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->order = $this->order ?? 'desc';
        $this->sort = $this->sort ?? 'Fecha';
        $this->fechaInicio = $this->fechaInicio ?? today()->format('Y-m-d');
        $this->fechaFin = $this->fechaFin ?? today()->format('Y-m-d');
        $this->sucursal = $this->sucursal ?? null;

        $this->sorts = ['Sucursal', 'Fecha'];
        $this->perPages = [10, 25, 50, 100];
        //        $this->filters = ['Activos', 'Inactivos', 'Todos'];
    }

    public function hydrate()
    {
        $this->dispatch('reApplySelect2');
    }
    public function updated()
    {
        $this->dispatch('reApplySelect2');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $res = $this->query();

        return view('livewire.reportes.ventas-diarias', [
            'records' => $res['finalRecords'],
            'grandTotal' => $res['grandTotal'],
            'sucursalesAll' => DB::table('tb_sucursales')
                ->select('id', 'nombre_comercial', 'razon_social')
                ->whereIn('id', user()->sucursales->pluck('id')->toArray())
                ->whereNull('deleted_at')
                ->where('cliente_id', user()->cliente_id)
                ->get()
                ->map(function ($value, $key) {
                    $nombre_comercial = Crypt::decrypt($value->nombre_comercial);
                    $razon_social = $value->razon_social ? Crypt::decrypt($value->razon_social) : '';
                    $label = $nombre_comercial . ($razon_social ? (" | $razon_social") : '');
                    return [
                        'value' => $value->id,
                        'label' => $label
                    ];
                })->toArray(),
            'formasPago' => $res['formasPago']
        ]);
    }

    public function query()
    {
        $query = DB::table('tb_ticket_operaciones as operacion')
            ->select(
                'sucursal.id as sucursal_id',
                'sucursal.nombre_comercial as sucursal',
                DB::raw("DATE(ticket.fecha_transaccion) as fecha_transaccion"),
                DB::raw("DATE_FORMAT(ticket.fecha_transaccion, '%d/%m/%Y') as fecha_transaccion_str"),
                'sfp.id as forma_pago_id',
                'sfp.nombre as forma_pago_nombre',
                DB::raw('SUM(operacion.monto) as monto'),
                DB::raw('COUNT(*) as operaciones')
            )
            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'operacion.ticket_id')
            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
            ->leftJoin('tb_sucursal_forma_pagos as sfp', 'sfp.id', 'operacion.sucursal_forma_pago_id')
            ->where('operacion.monto', '>', 0)
            ->where('sucursal.cliente_id', user()->cliente_id)
            ->groupByRaw('DATE(ticket.fecha_transaccion), operacion.sucursal_forma_pago_id');

        if ($this->fechaInicio) {
            $query->whereDate('ticket.fecha_transaccion', '>=', $this->fechaInicio);
        }
        if ($this->fechaFin) {
            $query->whereDate('ticket.fecha_transaccion', '<=', $this->fechaFin);
        }
        if ($this->sucursal) {
            $query->whereIn('ticket.sucursal_id', $this->sucursal);
        } else {
            $query->whereIn('ticket.sucursal_id', user()->sucursales->pluck('id')->toArray());
        }

        switch ($this->sort) {
            case 'Fecha':
                if ($this->order == 'asc')
                    $query->orderByRaw('DATE(ticket.fecha_transaccion) asc');
                else
                    $query->orderByRaw('DATE(ticket.fecha_transaccion) desc');
                break;
        }

        $formasPago = [];
        $records = $query->get()->each(function ($value, $key) use (&$formasPago) {
            $value->sucursal = Crypt::decrypt($value->sucursal);
            if (isset($formasPago[$value->forma_pago_id]) == false)
                $formasPago[$value->forma_pago_id] = $value->forma_pago_nombre;
        });

        switch ($this->sort) {
            case 'Sucursal':
                if ($this->order == 'asc')
                    $records = $records->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
        }

        $finalRecords = [];
        $grandTotal = [];

        foreach ($records as $record) {
            $sucursalId = $record->sucursal_id;
            $fecha = $record->fecha_transaccion;
            $formaPagoId = $record->forma_pago_id;

            if (!isset($finalRecords[$sucursalId])) {
                $finalRecords[$sucursalId] = [
                    'sucursal' => $record->sucursal,
                    'fechas' => [],
                    'totales' => [],
                ];
            }

            if (!isset($finalRecords[$sucursalId]['fechas'][$fecha])) {
                $finalRecords[$sucursalId]['fechas'][$fecha] = (object) [
                    'fecha_transaccion_str' => $record->fecha_transaccion_str,
                    'montos' => [],
                ];
            }

            // Detalle por fecha
            $actual = $finalRecords[$sucursalId]['fechas'][$fecha]->montos[$formaPagoId] ?? ['monto' => 0, 'operaciones' => 0];
            $finalRecords[$sucursalId]['fechas'][$fecha]->montos[$formaPagoId] = [
                'monto'       => $actual['monto'] + $record->monto,
                'operaciones' => $actual['operaciones'] + $record->operaciones,
            ];

            // Totalizador por sucursal
            $actualSucursal = $finalRecords[$sucursalId]['totales'][$formaPagoId] ?? ['monto' => 0, 'operaciones' => 0];
            $finalRecords[$sucursalId]['totales'][$formaPagoId] = [
                'monto'       => $actualSucursal['monto'] + $record->monto,
                'operaciones' => $actualSucursal['operaciones'] + $record->operaciones,
            ];

            // Totalizador general
            $actualGeneral = $grandTotal[$formaPagoId] ?? ['monto' => 0, 'operaciones' => 0];
            $grandTotal[$formaPagoId] = [
                'monto'       => $actualGeneral['monto'] + $record->monto,
                'operaciones' => $actualGeneral['operaciones'] + $record->operaciones,
            ];
        }

        return [
            'finalRecords' => $finalRecords,
            'grandTotal' => $grandTotal,
            'formasPago' => $formasPago
        ];
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }

    public function imprimirPdf()
    {
        if (File::exists(public_path('Ventas Diarias.pdf'))) {
            File::delete(public_path('Ventas Diarias.pdf'));
        }
        $name = 'Ventas Diarias';
        $view = 'reports.reportes.ventas-diarias.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            'name' => $name,
            'sorts' => $this->sorts,
            'records' => $this->query()['finalRecords'],
            'formasPago' => $this->query()['formasPago'],
            'grandTotal' => $this->query()['grandTotal'],
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'sucursalesSeleccionadas' => Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
                $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
            })->pluck('nombre_comercial')->toArray()
        ]);
        $pdf->save("$name.pdf");

        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name.pdf?" . time();
        $this->dispatch('show-sub-modal', 'pdf-ventas-diarias');
    }

    public function exportarExcel()
    {
        $name = 'Ventas Diarias';
        $fileName = "$name.xlsx";

        $res = $this->query();
        $sucursalesSeleccionadas = Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
            $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
        })->pluck('nombre_comercial')->toArray();
        return (new VentasDiariasExport(
            $name,
            $this->sorts,
            $res['finalRecords'],
            $res['formasPago'],
            $res['grandTotal'],
            $this->fechaInicio,
            $this->fechaFin,
            $sucursalesSeleccionadas
        ))
            ->download($fileName);
    }
}
