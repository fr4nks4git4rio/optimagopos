<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\ArticulosVendidosExport;
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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ArticulosVendidos extends Component
{
    public $order;
    public $sort = 'Artículo';
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

        $this->sorts = ['Artículo'];
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
        $res = $this->query();

        return view('livewire.reportes.articulos-vendidos', [
            'records' => $res['records'],
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
            'sucursales' => $res['sucursales']
        ]);
    }

    public function query()
    {
        $query = DB::table('tb_ticket_productos as t_producto')
            ->select(
                'sucursal.id as sucursal_id',
                'sucursal.nombre_comercial as sucursal',
                'producto.nombre as producto',
                DB::raw('SUM(t_producto.precio) as monto'),
                DB::raw('COUNT(*) as vendidos')
            )
            ->leftJoin('tb_tickets as ticket', 'ticket.id', 't_producto.ticket_id')
            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
            ->leftJoin('tb_productos as producto', 'producto.id', 't_producto.producto_id')
            ->groupByRaw('producto.id');

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
            case 'Artículo':
                if ($this->order == 'asc')
                    $query->orderByRaw('producto.nombre asc');
                else
                    $query->orderByRaw('producto.nombre desc');
                break;
        }

        $sucursales = [];
        $records = $query->get()->each(function ($value, $key) use (&$sucursales) {
            $sucursales[$value->sucursal_id] = Crypt::decrypt($value->sucursal);
        });

        switch ($this->sort) {
            case 'Artículo':
                if ($this->order == 'asc')
                    $records = $records->sortBy('producto', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('producto', SORT_NATURAL)->values();
                break;
        }

        $grandTotal = [];

        foreach ($records as $record) {
            $sucursalId = $record->sucursal_id;

            $record->montos = [];
            // Detalle por fecha
            $actual = $record->montos[$sucursalId] ?? ['monto' => 0, 'vendidos' => 0];
            $record->montos[$sucursalId] = [
                'monto'       => $actual['monto'] + $record->monto,
                'vendidos'    => $actual['vendidos'] + $record->vendidos,
            ];

            // Totalizador general
            $actualGeneral = $grandTotal[$sucursalId] ?? ['monto' => 0, 'vendidos' => 0];
            $grandTotal[$sucursalId] = [
                'monto'       => $actualGeneral['monto'] + $record->monto,
                'vendidos'    => $actualGeneral['vendidos'] + $record->vendidos,
            ];
        }

        return [
            'records' => $records,
            'grandTotal' => $grandTotal,
            'sucursales' => $sucursales
        ];
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }

    public function imprimirPdf()
    {
        if (File::exists(public_path('Artículos Vendidos.pdf'))) {
            File::delete(public_path('Artículos Vendidos.pdf'));
        }
        $name = 'Artículos Vendidos';
        $view = 'reports.reportes.articulos-vendidos.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            'name' => $name,
            'sorts' => $this->sorts,
            'records' => $this->query()['records'],
            'sucursales' => $this->query()['sucursales'],
            'grandTotal' => $this->query()['grandTotal'],
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'sucursalesSeleccionadas' => Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
                $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
            })->pluck('nombre_comercial')->toArray()
        ]);
        $pdf->save("$name.pdf");

        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name.pdf?" . time();
        $this->iframeContainerClass = 'show';
    }

    public function exportarExcel()
    {
        $name = 'Artículos Vendidos';
        $fileName = "$name.xlsx";

        $res = $this->query();

        $sucursalesSeleccionadas = Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
            $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
        })->pluck('nombre_comercial')->toArray();
        return (new ArticulosVendidosExport($name, $this->sorts, $res['records'], $res['sucursales'], $res['grandTotal'], $this->fechaInicio, $this->fechaFin, $sucursalesSeleccionadas))
            ->download($fileName);
    }
}
