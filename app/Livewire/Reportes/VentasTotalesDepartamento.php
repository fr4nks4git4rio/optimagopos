<?php

namespace App\Livewire\Reportes;

use App\Exports\VentasDepartamentoExport;
use App\Exports\VentasOperadorExport;
use App\Exports\VentasTotalesDepartamentoExport;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class VentasTotalesDepartamento extends Component
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
        $this->sort = $this->sort ?? __('site.reports.total_sales_by_department.department');
        $this->fechaInicio = $this->fechaInicio ?? today()->format('Y-m-d');
        $this->fechaFin = $this->fechaFin ?? today()->format('Y-m-d');
        $this->sucursal = $this->sucursal ?? null;

        $this->sorts = [__('site.reports.total_sales_by_department.branch'), __('site.reports.total_sales_by_department.department')];
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
        if (user()->cannot('reportsTotalSalesByDepartment-viewAny')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
    }

    public function render()
    {
        $res = $this->query();

        $finalRecords = $res['finalRecords'];
        $grandTotal = $res['grandTotal'];

        return view('livewire.reportes.ventas-totales-departamento', [
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
        $query = DB::table('tb_ticket_productos as tp')
            ->select(
                'sucursal.id as sucursal_id',
                'sucursal.nombre_comercial as sucursal',
                'departamento.id as departamento_id',
                'departamento.nombre as departamento',
                DB::raw('SUM(tp.precio) as ventas_importe'),
                DB::raw('COUNT(DISTINCT ticket.id) as ventas_cant')
            )
            ->leftJoin('tb_tickets as ticket', 'ticket.id', 'tp.ticket_id')
            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
            ->leftJoin('tb_departamentos as departamento', 'departamento.id', 'tp.departamento_id')
            ->whereNotNull('tp.departamento_id')
            ->where('ticket.modo_entrenamiento', 0)
            ->groupBy('sucursal.id', 'departamento.id');

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
        });

        switch ($this->sort) {
            case __('site.reports.total_sales_by_operator.branch'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
            case __('site.reports.total_sales_by_operator.departamento'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('departamento', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('departamento', SORT_NATURAL)->values();
                break;
        }

        $finalRecords = [];
        $grandTotal = null;

        foreach ($records as $record) {
            $sucursalId = $record->sucursal_id;
            $departamento = $record->departamento;
            $departamento_id = $record->departamento_id;

            if (!isset($finalRecords[$sucursalId])) {
                $finalRecords[$sucursalId] = [
                    'sucursal' => $record->sucursal,
                    'departamentos' => [],
                    'totales' => null,
                ];
            }

            if (!isset($finalRecords[$sucursalId]['departamentos'][$departamento_id])) {
                $finalRecords[$sucursalId]['departamentos'][$departamento_id] = (object) [
                    'nombre' => $departamento,
                    'ventas_cant' => $record->ventas_cant,
                    'ventas_importe' => $record->ventas_importe
                ];
            }

            // Totalizador por sucursal
            $actualSucursal = $finalRecords[$sucursalId]['totales'] ?? ['ventas_cant' => 0, 'ventas_importe' => 0];
            $finalRecords[$sucursalId]['totales'] = [
                'ventas_cant'          => $actualSucursal['ventas_cant'] + $record->ventas_cant,
                'ventas_importe'       => $actualSucursal['ventas_importe'] + $record->ventas_importe
            ];

            // Totalizador general
            $actualGeneral = $grandTotal ?? ['ventas_cant' => 0, 'ventas_importe' => 0];
            $grandTotal = [
                'ventas_cant'          => $actualGeneral['ventas_cant'] + $record->ventas_cant,
                'ventas_importe'       => $actualGeneral['ventas_importe'] + $record->ventas_importe
            ];
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
        if (user()->cannot('reportsTotalSalesByDepartment-print')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return;
        }
        $name = __('site.reports.total_sales_by_department.title');
        if (File::exists(public_path("$name.pdf"))) {
            File::delete(public_path("$name.pdf"));
        }
        $view = 'reports.reportes.ventas-totales-departamento.pdf';

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
        $this->dispatch('show-sub-modal', 'pdf-ventas-totales-departamento');
    }

    public function exportarExcel()
    {
        if (user()->cannot('reportsTotalSalesByDepartment-export')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return;
        }
        $name = __('site.reports.total_sales_by_department.title');
        $fileName = "$name.xlsx";

        $res = $this->query();

        $sucursalesSeleccionadas = Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
            $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
        })->pluck('nombre_comercial')->toArray();
        return (new VentasTotalesDepartamentoExport($name, $this->sorts, $res['finalRecords'], $res['grandTotal'], $this->fechaInicio, $this->fechaFin, $sucursalesSeleccionadas))
            ->download($fileName);
    }
}
