<?php

namespace App\Livewire\Reportes;

use App\Exports\MovimientosCajaExport;
use App\Exports\VentasDepartamentoExport;
use App\Exports\VentasOperadorExport;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class MovimientosCaja extends Component
{
    public $perPages;
    public $order;
    public $sort;
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
        $this->sort = $this->sort ?? __('site.reports.cash_movements.branch');
        $this->fechaInicio = $this->fechaInicio ?? today()->format('Y-m-d');
        $this->fechaFin = $this->fechaFin ?? today()->format('Y-m-d');
        $this->sucursal = $this->sucursal ?? null;

        $this->sorts = [
            __('site.reports.cash_movements.branch'),
            __('site.reports.cash_movements.date'),
            __('site.reports.cash_movements.movement_type'),
            __('site.reports.cash_movements.payment_form'),
            __('site.reports.cash_movements.created_by'),
            __('site.reports.cash_movements.amount'),
        ];
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

    public function init()
    {
        if (user()->cannot('reportsCashMovements-viewAny')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
    }

    public function render()
    {
        $res = $this->query();

        $finalRecords = $res['finalRecords'];
        $grandTotal = $res['grandTotal'];

        return view('livewire.reportes.movimientos-caja', [
            'records' => $finalRecords,
            'grandTotal' => $grandTotal,
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
                })->toArray()
        ]);
    }

    public function query()
    {
        $query = DB::table('tb_ticket_movimientos_caja as tmc')
            ->select(
                'sucursal.id as sucursal_id',
                'sucursal.nombre_comercial as sucursal',
                'tmc.nombre as movimiento',
                'tmc.monto',
                'form_pago.nombre as forma_pago',
                'empleado.nombre as creado_por',
                'ticket.fecha_transaccion as fecha',
                DB::raw("DATE_FORMAT(ticket.fecha_transaccion, '%d/%m/%Y') as fecha_str")
            )
            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tmc.ticket_id')
            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
            ->leftJoin('tb_sucursal_forma_pagos as form_pago', 'form_pago.id', 'tmc.sucursal_forma_pago_id')
            ->leftJoin('tb_empleados as empleado', 'empleado.id', 'ticket.empleado_id')
            ->where('ticket.modo_entrenamiento', 0);


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

        $records = $query->get()->each(function ($value, $key) {
            $value->sucursal = Crypt::decrypt($value->sucursal);
            $value->creado_por = $value->creado_por ? Crypt::decrypt($value->creado_por) : '';
        });

        switch ($this->sort) {
            case __('site.reports.cash_movements.branch'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
            case __('site.reports.cash_movements.date'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('fecha', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('fecha', SORT_NATURAL)->values();
                break;
            case __('site.reports.cash_movements.movement_type'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('movimiento', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('movimiento', SORT_NATURAL)->values();
                break;
            case __('site.reports.cash_movements.payment_form'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('forma_pago', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('forma_pago', SORT_NATURAL)->values();
                break;
            case __('site.reports.cash_movements.created_by'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('creado_por', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('creado_por', SORT_NATURAL)->values();
                break;
            case __('site.reports.cash_movements.amount'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('monto', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('monto', SORT_NATURAL)->values();
                break;
        }

        $finalRecords = [];
        $grandTotal = 0;

        foreach ($records as $record) {
            $sucursalId = $record->sucursal_id;

            if (!isset($finalRecords[$sucursalId])) {
                $finalRecords[$sucursalId] = [
                    'sucursal' => $record->sucursal,
                    'movimientos' => [],
                    'total' => 0,
                ];
            }

            $finalRecords[$sucursalId]['movimientos'][] = $record;

            // Totalizador por sucursal
            $finalRecords[$sucursalId]['total'] += $record->monto;

            // Totalizador general
            $grandTotal += $record->monto;
        }

        return [
            'finalRecords' => $finalRecords,
            'grandTotal' => $grandTotal,
        ];
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }

    public function imprimirPdf()
    {
        if (user()->cannot('reportsCashMovements-print')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return;
        }
        $name = __('site.reports.cash_movements.title');
        if (File::exists(public_path("$name.pdf"))) {
            File::delete(public_path("$name.pdf"));
        }
        $view = 'reports.reportes.movimientos-caja.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            'name' => $name,
            'sorts' => $this->sorts,
            'records' => $this->query()['finalRecords'],
            'grandTotal' => $this->query()['grandTotal'],
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'sucursalesSeleccionadas' => Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
                $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
            })->pluck('nombre_comercial')->toArray()
        ]);
        $pdf->save("$name.pdf");

        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name.pdf?" . time();
        $this->dispatch('show-sub-modal', 'pdf-movimientos-caja');
    }

    public function exportarExcel()
    {
        if (user()->cannot('reportsCashMovements-export')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return;
        }
        $name = __('site.reports.cash_movements.title');
        $fileName = "$name.xlsx";

        $res = $this->query();

        $sucursalesSeleccionadas = Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
            $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
        })->pluck('nombre_comercial')->toArray();
        return (new MovimientosCajaExport($name, $this->sorts, $res['finalRecords'], $res['grandTotal'], $this->fechaInicio, $this->fechaFin, $sucursalesSeleccionadas))
            ->download($fileName);
    }
}
