<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\FacturaEmitidaExport;
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
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class VentasPeriodo extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $perPages;
    public $order;
    public $sort = 'Fecha';
    public $sorts;
    public $fechaInicio;
    public $fechaFin;
    public $sucursal;

    public $sucursales = [];
    public $iframeContainerClass = '';
    public $iframeSrc = '';
    //    public $filter = 'Activos';
    //    public $filters;

    protected $queryString = [
        'perPage' => ['except' => null],
        'order' => ['except' => null],
        'sort' => ['except' => null],
        'fechaInicio' => ['except' => null],
        'fechaFin' => ['except' => null],
        'sucursal' => ['except' => null]
    ];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->perPage = $this->perPage ?? 10;
        $this->order = $this->order ?? 'desc';
        $this->sort = $this->sort ?? 'Fecha';
        $this->fechaInicio = $this->fechaInicio ?? today()->format('Y-m-d');
        $this->fechaFin = $this->fechaFin ?? today()->format('Y-m-d');
        $this->sucursal = $this->sucursal ?? null;

        $this->sorts = ['Fecha', 'Sucursal', 'Importe MXN', 'Importe USD'];
        $this->perPages = [10, 25, 50, 100];
        //        $this->filters = ['Activos', 'Inactivos', 'Todos'];
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }
    public function updated()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $records = $this->query()->get()->map(function ($value, $key) {
            $value->sucursal = Crypt::decrypt($value->sucursal);
            return $value;
        });
        switch ($this->sort) {
            case 'Sucursal':
                if ($this->order == 'asc')
                    $records = $records->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
            case 'Importe MXN':
                if ($this->order == 'asc')
                    $records = $records->sortBy('monto_mxn', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('monto_mxn', SORT_NATURAL)->values();
                break;
            case 'Importe USD':
                if ($this->order == 'asc')
                    $records = $records->sortBy('monto_usd', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('monto_usd', SORT_NATURAL)->values();
                break;
        }

        $total = $records->count();
        $records = $records->forPage($this->page, $this->perPage);
        $records = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.reportes.ventas-periodo', [
            'records' => $records,
        ]);
    }

    public function init()
    {
        $this->sucursales = DB::table('tb_sucursales')
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
            })->toArray();
    }

    public function query()
    {
        $query = DB::table('tb_ticket_operaciones as operacion')
            ->select(
                'sucursal.nombre_comercial as sucursal',
                DB::raw("DATE(ticket.fecha_transaccion) as fecha_transaccion"),
                DB::raw("DATE_FORMAT(ticket.fecha_transaccion, '%d/%m/%Y') as fecha_transaccion_str"),
                DB::raw("SUM(IF(moneda.id = 1, operacion.monto, 0)) as monto_mxn"),
                DB::raw("SUM(IF(moneda.id = 2, operacion.monto, 0)) as monto_usd")
            )
            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'operacion.ticket_id')
            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
            ->leftJoin('tb_sucursal_forma_pagos as forma_pago', 'forma_pago.id', 'operacion.sucursal_forma_pago_id')
            ->leftJoin('tb_monedas as moneda', 'moneda.id', 'forma_pago.moneda_id')
            ->groupByRaw('DATE(ticket.fecha_transaccion)');

        if ($this->fechaInicio) {
            $query->whereDate('ticket.fecha_transaccion', '>=', $this->fechaInicio);
        }
        if ($this->fechaFin) {
            $query->whereDate('ticket.fecha_transaccion', '<=', $this->fechaFin);
        }
        if ($this->sucursal) {
            $query->where('ticket.sucursal_id', $this->sucursal);
        }

        switch ($this->sort) {
            case 'Fecha':
                if ($this->order == 'asc')
                    $query->orderByRaw('DATE(ticket.fecha_transaccion) asc');
                else
                    $query->orderByRaw('DATE(ticket.fecha_transaccion) desc');
                break;
        }

        return $query;
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
