<?php

namespace App\Http\Livewire\Reportes;

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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class HistoricoTicketsVk extends Component
{
    public $perPages;
    public $order;
    public $sort = 'Sucursal';
    public $sorts;
    public $fechaInicio;
    public $fechaFin;
    public $estado = [];
    public $terminal = [];
    public $sucursal = [];
    public $terminalesAll = [];
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
        $this->sort = $this->sort ?? 'Sucursal';
        $this->fechaInicio = $this->fechaInicio ?? today()->format('Y-m-d');
        $this->fechaFin = $this->fechaFin ?? today()->format('Y-m-d');
        $this->sucursal = $this->sucursal ?? null;

        $this->sorts = ['Sucursal', 'Ticket', 'Terminal'];
        $this->perPages = [10, 25, 50, 100];

        $this->loadTerminales();
        //        $this->filters = ['Activos', 'Inactivos', 'Todos'];
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }
    public function updated($field, $value)
    {
        if ($field == 'sucursal') {
            if (!$value)
                $this->terminal = [];
            $this->loadTerminales();
        }
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $res = $this->query();

        return view('livewire.reportes.historico-tickets-vk', [
            'records' => $res['finalRecords'],
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
            'estadosAll' => [
                ['value' => 'open', 'label' => 'ABIERTO'],
                ['value' => 'in_process', 'label' => 'EN PROCESO'],
                ['value' => 'delayed', 'label' => 'DEMORADO'],
                ['value' => 'done', 'label' => 'TERMINADO'],
            ]
        ]);
    }

    public function query()
    {
        $query = DB::table('tb_tickets_vk as ticket')
            ->select(
                'ticket.id_transaccion',
                'ticket.fecha_transaccion',
                'ticket.fecha_en_proceso',
                'ticket.fecha_demorado',
                'ticket.fecha_terminado',
                DB::raw("DATE_FORMAT(ticket.fecha_transaccion, '%d/%m/%Y %H:%i:%s') as fecha_transaccion_str"),
                DB::raw("DATE_FORMAT(ticket.fecha_en_proceso, '%d/%m/%Y %H:%i:%s') as fecha_en_proceso_str"),
                DB::raw("DATE_FORMAT(ticket.fecha_demorado, '%d/%m/%Y %H:%i:%s') as fecha_demorado_str"),
                DB::raw("DATE_FORMAT(ticket.fecha_terminado, '%d/%m/%Y %H:%i:%s') as fecha_terminado_str"),
                DB::raw("'' as tiempo_abierto"),
                DB::raw("'' as tiempo_en_proceso"),
                DB::raw("'' as tiempo_demorado"),
                'sucursal.id as sucursal_id',
                'sucursal.nombre_comercial as sucursal',
                'terminal.nombre as terminal'
            )
            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'ticket.sucursal_id')
            ->leftJoin('tb_terminales as terminal', 'terminal.id', 'ticket.terminal_id');

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
        if ($this->terminal) {
            $query->whereIn('ticket.terminal_id', $this->terminal);
        } else {
            $query->whereIn('ticket.terminal_id', user()->terminales->pluck('id')->toArray());
        }

        if ($this->estado) {
            if (in_array('open', $this->estado)) {
                $query->whereNotNull('fecha_transaccion');
            }
            if (in_array('in_process', $this->estado)) {
                $query->whereNotNull('fecha_en_proceso');
            }
            if (in_array('delayed', $this->estado)) {
                $query->whereNotNull('fecha_demorado');
            }
            if (in_array('done', $this->estado)) {
                $query->whereNotNull('fecha_terminado');
            }
        }

        switch ($this->sort) {
            case 'Ticket':
                if ($this->order == 'asc')
                    $query->orderBy('ticket.id_transaccion');
                else
                    $query->orderByDesc('ticket.id_transaccion');
                break;
            case 'Terminal':
                if ($this->order == 'asc')
                    $query->orderByRaw('terminal.nombre asc');
                else
                    $query->orderByRaw('terminal.nombre desc');
                break;
        }

        $records = $query->get()->each(function ($value, $key) {
            $value->sucursal = Crypt::decrypt($value->sucursal);
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

        foreach ($records as $record) {
            $sucursalId = $record->sucursal_id;

            if (!isset($finalRecords[$sucursalId])) {
                $finalRecords[$sucursalId] = [
                    'sucursal' => $record->sucursal,
                    'records' => []
                ];
            }

            $referencia = $record->fecha_terminado ? Carbon::parse($record->fecha_terminado) : Carbon::now();

            $record->tiempo_abierto     = $record->fecha_transaccion ? $this->formatDuration($record->fecha_transaccion, $referencia)    : null;
            $record->tiempo_en_proceso  = $record->fecha_en_proceso  ? $this->formatDuration($record->fecha_en_proceso, $referencia)     : null;
            $record->tiempo_demorado    = $record->fecha_demorado    ? $this->formatDuration($record->fecha_demorado, $referencia)       : null;

            $finalRecords[$sucursalId]['records'][] = $record;
        }

        return [
            'finalRecords' => $finalRecords
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
        ]);
        $pdf->save("$name.pdf");

        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name.pdf?" . time();
        $this->iframeContainerClass = 'show';
    }

    public function exportarExcel()
    {
        $name = 'Ventas Diarias';
        $fileName = "$name.xlsx";

        $res = $this->query();

        return (new VentasDiariasExport($name, $this->sorts, $res['finalRecords'], $res['formasPago'], $res['grandTotal']))
            ->download($fileName);
    }

    private function loadTerminales()
    {
        $query = DB::table('tb_terminales as terminal')
            ->leftJoin('tb_sucursales as sucursal', 'sucursal.id', 'terminal.sucursal_id')
            ->select('terminal.id', 'terminal.nombre')
            ->whereIn('terminal.id', user()->terminales->pluck('id')->toArray())
            ->whereNull('terminal.deleted_at')
            ->where('sucursal.cliente_id', user()->cliente_id);

        if (count($this->sucursal) > 0) {
            $query->whereIn('sucursal.id', $this->sucursal);
        }
        $this->terminalesAll = $query->get()
            ->map(function ($value, $key) {
                return [
                    'value' => $value->id,
                    'label' => $value->nombre
                ];
            })->toArray();
    }

    private function formatDuration($desde, $hasta): string
    {
        $desde = Carbon::parse($desde);
        $hasta = Carbon::parse($hasta);

        $diff = $desde->diff($hasta);

        $partes = [];
        if ($diff->d > 0) $partes[] = $diff->d . 'd';
        if ($diff->h > 0) $partes[] = $diff->h . 'h';
        if ($diff->i > 0) $partes[] = $diff->i . 'm';
        if ($diff->s > 0) $partes[] = $diff->s . 's';

        return $partes ? implode(' ', $partes) : '0s';
    }
}
