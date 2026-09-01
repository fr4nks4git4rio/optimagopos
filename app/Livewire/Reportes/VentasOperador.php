<?php

namespace App\Livewire\Reportes;

use App\Exports\VentasOperadorExport;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class VentasOperador extends Component
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
        $this->sort = $this->sort ?? __('site.reports.sales_by_operator.operator');
        $this->fechaInicio = $this->fechaInicio ?? today()->format('Y-m-d');
        $this->fechaFin = $this->fechaFin ?? today()->format('Y-m-d');
        $this->sucursal = $this->sucursal ?? null;

        $this->sorts = [__('site.reports.sales_by_operator.branch'), __('site.reports.sales_by_operator.operator')];
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
        if (user()->cannot('reportsSalesByOperator-viewAny')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
    }

    public function render()
    {
        $res = $this->query();

        $finalRecords = $res['finalRecords'];
        $grandTotal = $res['grandTotal'];

        return view('livewire.reportes.ventas-operador', [
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
        $operacionesSub = DB::table('tb_ticket_operaciones')
            ->select(
                'ticket_id',
                DB::raw('SUM(monto) as ventas_importe')
            )
            ->groupBy('ticket_id');

        $correccionesSub = DB::table('tb_ticket_producto_correcciones')
            ->select(
                'ticket_id',
                DB::raw('SUM(precio) as correcciones_importe'),
                DB::raw('COUNT(id) as correcciones_cant')
            )
            ->groupBy('ticket_id');

        $query = DB::table('tb_tickets as ticket')
            ->select(
                'sucursal.id as sucursal_id',
                'sucursal.nombre_comercial as sucursal',
                'empleado.id as empleado_id',
                'empleado.nombre as empleado',
                'empleado.id_empleado as id_empleado',
                DB::raw('COALESCE(SUM(op.ventas_importe), 0) as ventas_importe'),
                DB::raw('COUNT(ticket.id) as ventas_cant'),
                DB::raw('COALESCE(SUM(cor.correcciones_importe), 0) as correcciones_importe'),
                DB::raw('COALESCE(SUM(cor.correcciones_cant), 0) as correcciones_cant')
            )
            ->leftJoinSub($operacionesSub, 'op', 'op.ticket_id', 'ticket.id')
            ->leftJoinSub($correccionesSub, 'cor', 'cor.ticket_id', 'ticket.id')
            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
            ->leftJoin('tb_empleados as empleado', 'empleado.id', 'ticket.empleado_id')
            ->where('ticket.modo_entrenamiento', 0)
            ->groupBy('sucursal.id', 'empleado.id');

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
            $value->empleado = Crypt::decrypt($value->empleado);
        });

        switch ($this->sort) {
            case __('site.reports.sales_by_operator.branch'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
            case __('site.reports.sales_by_operator.operator'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('empleado', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('empleado', SORT_NATURAL)->values();
                break;
        }

        $finalRecords = [];
        $grandTotal = null;

        foreach ($records as $record) {
            $sucursalId = $record->sucursal_id;
            $empleado = $record->empleado;
            $empleado_id = $record->empleado_id;

            if (!isset($finalRecords[$sucursalId])) {
                $finalRecords[$sucursalId] = [
                    'sucursal' => $record->sucursal,
                    'operadores' => [],
                    'totales' => null,
                ];
            }

            if (!isset($finalRecords[$sucursalId]['operadores'][$empleado_id])) {
                $finalRecords[$sucursalId]['operadores'][$empleado_id] = (object) [
                    'nombre' => "$empleado (ID: $record->id_empleado)",
                    'ventas_cant' => $record->ventas_cant,
                    'ventas_importe' => $record->ventas_importe,
                    'correcciones_cant' => $record->correcciones_cant,
                    'correcciones_importe' => $record->correcciones_importe,
                ];
            }

            // Totalizador por sucursal
            $actualSucursal = $finalRecords[$sucursalId]['totales'] ?? ['ventas_cant' => 0, 'ventas_importe' => 0, 'correcciones_cant' => 0, 'correcciones_importe' => 0];
            $finalRecords[$sucursalId]['totales'] = [
                'ventas_cant'          => $actualSucursal['ventas_cant'] + $record->ventas_cant,
                'ventas_importe'       => $actualSucursal['ventas_importe'] + $record->ventas_importe,
                'correcciones_cant'    => $actualSucursal['correcciones_cant'] + $record->correcciones_cant,
                'correcciones_importe' => $actualSucursal['correcciones_importe'] + $record->correcciones_importe,
            ];

            // Totalizador general
            $actualGeneral = $grandTotal ?? ['ventas_cant' => 0, 'ventas_importe' => 0, 'correcciones_cant' => 0, 'correcciones_importe' => 0];
            $grandTotal = [
                'ventas_cant'          => $actualGeneral['ventas_cant'] + $record->ventas_cant,
                'ventas_importe'       => $actualGeneral['ventas_importe'] + $record->ventas_importe,
                'correcciones_cant'    => $actualGeneral['correcciones_cant'] + $record->correcciones_cant,
                'correcciones_importe' => $actualGeneral['correcciones_importe'] + $record->correcciones_importe,
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
        if (user()->cannot('reportsSalesByOperator-print')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return;
        }
        $name = __('site.reports.sales_by_operator.title');
        if (File::exists(public_path("$name.pdf"))) {
            File::delete(public_path("$name.pdf"));
        }
        $view = 'reports.reportes.ventas-operador.pdf';

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
        $this->dispatch('show-sub-modal', 'pdf-ventas-operador');
    }

    public function exportarExcel()
    {
        if (user()->cannot('reportsSalesByOperator-export')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return;
        }
        $name = __('site.reports.sales_by_operator.title');
        $fileName = "$name.xlsx";

        $res = $this->query();

        $sucursalesSeleccionadas = Sucursal::whereIn('id', $this->sucursal)->get()->each(function ($element) {
            $element->nombre_comercial = Crypt::decrypt($element->nombre_comercial);
        })->pluck('nombre_comercial')->toArray();
        return (new VentasOperadorExport($name, $this->sorts, $res['finalRecords'], $res['grandTotal'], $this->fechaInicio, $this->fechaFin, $sucursalesSeleccionadas))
            ->download($fileName);
    }
}
