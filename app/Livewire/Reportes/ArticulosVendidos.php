<?php

namespace App\Livewire\Reportes;

use App\Exports\ArticulosVendidosExport;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class ArticulosVendidos extends Component
{
    public $order;
    public $sort;
    public $sorts;
    public $fechaInicio;
    public $fechaFin;
    public $sucursal = [];
    public $iframeContainerClass = '';
    public $sucursalesAll = [];
    public $iframeSrc = '';
    //    public $filter = 'Activos';
    //    public $filters;

    protected $queryString = [
        'order' => ['except' => ''],
        'sort' => ['except' => ''],
        'fechaInicio' => ['except' => ''],
        'fechaFin' => ['except' => '']
    ];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->order ??= 'desc';
        $this->sort ??= __('site.reports.articles_sold.article');
        $this->fechaInicio ??= today()->format('Y-m-d');
        $this->fechaFin ??= today()->format('Y-m-d');
        $this->sucursal ??= null;

        $this->sorts = [__('site.reports.articles_sold.article')];
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

        return view('livewire.reportes.articulos-vendidos', [
            'records' => $res['records'],
            'grandTotal' => $res['grandTotal'],
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
            ->where('sucursal.cliente_id', user()->cliente_id)
            ->groupByRaw('producto.id');

        if ($this->fechaInicio) {
            $query->whereDate('ticket.fecha_transaccion', '>=', $this->fechaInicio);
        }
        if ($this->fechaFin) {
            $query->whereDate('ticket.fecha_transaccion', '<=', $this->fechaFin);
        }
        if ($this->sucursal) {
            $query->whereIn('sucursal.id', $this->sucursal);
        } else {
            $query->whereIn('sucursal.id', user()->sucursales->pluck('id')->toArray());
        }

        switch ($this->sort) {
            case __('site.reports.articles_sold.article'):
                if ($this->order == 'asc')
                    $query->orderByRaw('producto.nombre asc');
                else
                    $query->orderByRaw('producto.nombre desc');
                break;
        }

        $sucursales = [];
        $records = $query->get()->each(function ($value, $key) use (&$sucursales) {
            if (!isset($sucursales[$value->sucursal_id])) {
                $sucursales[$value->sucursal_id] = Crypt::decrypt($value->sucursal);

                $this->sucursalesAll[] = [
                    'value' => $value->sucursal_id,
                    'label' => Crypt::decrypt($value->sucursal)
                ];
            }
        });

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
        $name = __('site.reports.articles_sold.title');
        if (File::exists(public_path("$name.pdf"))) {
            File::delete(public_path("$name.pdf"));
        }
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
        $this->dispatch('show-sub-modal', 'pdf-articulos-vendidos');
    }

    public function exportarExcel()
    {
        $name = __('site.reports.articles_sold.title');
        $fileName = "$name.xlsx";

        $res = $this->query();

        $sucursalesSeleccionadas = Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
            $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
        })->pluck('nombre_comercial')->toArray();
        return (new ArticulosVendidosExport($name, $this->sorts, $res['records'], $res['sucursales'], $res['grandTotal'], $this->fechaInicio, $this->fechaFin, $sucursalesSeleccionadas))
            ->download($fileName);
    }
}
