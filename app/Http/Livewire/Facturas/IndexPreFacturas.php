<?php

namespace App\Http\Livewire\Facturas;

use App\Exports\FacturaEmitidaExport;
use App\Http\Libraries\Pdf;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Moneda;
use App\Services\Timbrado\Facturador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPreFacturas extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $perPages;
    public $search;
    public $order;
    public $sort;
    public $sorts;
    public $fechaInicio;
    public $fechaFin;
    public $cliente;
    public $estado;
    public $estados;
    public $moneda;
    public $monedas;
    public $importe;
    public $iframeContainerClass = '';
    public $iframeSrc = '';
    //    public $filter = 'Activos';
    //    public $filters;

    protected $queryString = [
        'search' => ['except' => null],
        'perPage' => ['except' => null],
        'sort' => ['except' => null],
        'fechaInicio' => ['except' => null],
        'fechaFin' => ['except' => null],
        'cliente' => ['except' => null],
        'estado' => ['except' => null],
        'moneda' => ['except' => null],
        'importe' => ['except' => null]
    ];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? null;
        $this->order = $this->order ?? 'desc';
        $this->sort = $this->sort ?? 'Fecha';
        $this->fechaInicio = $this->fechaInicio ?? null;
        $this->fechaFin = $this->fechaFin ?? null;
        $this->cliente = $this->cliente ?? null;
        $this->estado = $this->estado ?? 'Todos';
        $this->moneda = $this->moneda ?? 'Todas';
        $this->importe = $this->importe ?? null;
        $this->sorts = ['Fecha', 'Receptor', 'Estado', 'Moneda', 'Subtotal', 'IVA', 'Total'];
        $this->perPages = [10, 25, 50, 100];
        $this->estados = ['Todos', 'PRECAPTURADA', 'CAPTURADA'];
        $this->monedas = Moneda::all()->pluck('acronimo')->prepend('Todas');
        //        $this->filters = ['Activos', 'Inactivos', 'Todos'];
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $facturas = $this->query();
        $total = $facturas->count();
        $records = $facturas->forPage($this->page, $this->perPage);
        $facturas = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.facturas.index-pre-facturas', [
            'facturas' => $facturas,
        ]);
    }

    public function init()
    {
        if ($this->cliente) {
            $cliente = Cliente::find($this->cliente);
            $cliente = Cliente::decryptInfo($cliente);
            $this->dispatchBrowserEvent('set-data-cliente', ['data' => [['id' => $cliente->id, 'text' => $cliente->nombre_comercial]], 'term' => '', 'value' => $this->cliente]);
        }
    }

    public function query()
    {
        $query = DB::table('tb_facturas as factura')
            ->select(
                'factura.id',
                DB::raw('DATE_FORMAT(factura.fecha_emision, "%Y-%m-%d %H:%i") as fecha_emision_sort'),
                DB::raw('DATE_FORMAT(factura.fecha_emision, "%d/%m/%Y") as fecha_emision_str'),
                'cliente.razon_social as receptor',
                'cliente.rfc as rfc_receptor',
                'propietario.razon_social as emisor',
                'propietario.rfc as rfc_emisor',
                'factura.estado',
                'factura.moneda',
                'factura.subtotal',
                'factura.iva',
                'factura.total',
                DB::raw("(SELECT GROUP_CONCAT(fc.descripcion SEPARATOR '
                ') FROM tb_factura_conceptos as fc WHERE fc.factura_id = factura.id) as conceptos")
            )
            ->leftJoin('tb_clientes as cliente', 'factura.cliente_id', '=', 'cliente.id')
            ->leftJoin('tb_clientes as propietario', 'factura.propietario_id', '=', 'propietario.id')
            ->distinct('factura.id')
            ->where('factura.user_id', '>', 0);
        if ($this->fechaInicio) {
            $query->where('factura.fecha_certificacion', '>=', $this->fechaInicio);
        }
        if ($this->fechaFin) {
            $query->where('factura.fecha_certificacion', '<=', $this->fechaFin);
        }
        if ($this->cliente) {
            $query->where('factura.cliente_id', $this->cliente);
        }
        if ($this->estado && $this->estado != 'Todos') {
            $query->where('factura.estado', $this->estado);
        } else {
            $query->whereIn('factura.estado', ['PRECAPTURADA', 'CAPTURADA']);
        }
        if ($this->moneda && $this->moneda != 'Todas') {
            $query->where('factura.moneda', $this->moneda);
        }
        if ($this->importe) {
            $query->where('factura.total', 'like', "%$this->importe%");
        }

        $records = $query->get();

        $final_records = collect();
        foreach ($records as $record) {
            $record->receptor = strtoupper(Crypt::decrypt($record->receptor));
            if (
                !$this->search
                || str_contains($record->fecha_emision_str, $this->search)
                || str_contains($record->receptor, $this->search)
                || str_contains($record->estado, $this->search)
                || str_contains($record->moneda, $this->search)
                || str_contains($record->subtotal, $this->search)
                || str_contains($record->iva, $this->search)
                || str_contains($record->total, $this->search)
            ) {
                $final_records->push($record);
            }
        }

        switch ($this->sort) {
            case 'Fecha':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('fecha_certificacion_sort', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('fecha_certificacion_sort', SORT_NATURAL)->values();
                break;
            case 'Receptor':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('receptor', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('receptor', SORT_NATURAL)->values();
                break;
            case 'Estado':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('estado', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('estado', SORT_NATURAL)->values();
                break;
            case 'Moneda':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('moneda', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('moneda', SORT_NATURAL)->values();
                break;
            case 'Subtotal':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('subtotal', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('subtotal', SORT_NATURAL)->values();
                break;
            case 'IVA':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('iva', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('iva', SORT_NATURAL)->values();
                break;
            case 'Total':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('total', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('total', SORT_NATURAL)->values();
                break;
        }

        return $final_records;
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }

    public function nuevaFactura()
    {
        $tipo_cambio = get_tipo_cambio();
        if (!$tipo_cambio->id) {
            $this->emit('show-toast', 'Debe definir el tipo de cambio para el día de hoy.', 'danger');
            return;
        }
        $cliente_publico_general = DB::table('tb_clientes')->where('rfc', 'XAXX010101000')->get()->first();
        if (!$cliente_publico_general) {
            $this->emit('show-toast', 'Primero debe dar de alta al Cliente: "VENTA A PUBLICO GENERAL" con RFC: "XAXX010101000".', 'danger');
            return;
        }
        return redirect()->route('pre-facturas.save');
    }

    public function timbrar($id)
    {
        $factura = Factura::find($id);
        $folio_interno = $factura->serie->descripcion . '-' . Factura::internalSheetGenerator($factura->serie_id, modo_facturacion() == 1);
        $facturador = new Facturador($factura->propietario);
        $res = $facturador->timbrarFactura($id, $folio_interno);
        if ($res['success']) {
            $this->emit('show-toast', "Factura timbrada satisfactoriamente.");
        } else {
            $this->emit('show-toast', pretty_message($res['message'], 'danger'), 'danger');
        }
    }

    public function showPdf($id)
    {
        $factura = Factura::find($id);
        $name = Factura::generateFacturaPdf($id, true);
        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name?" . time();
        $this->iframeContainerClass = 'show';
    }

    public function imprimirFacturas()
    {
        $facturas = $this->query();

        activity('Pre-Facturas')
            ->causedBy(auth()->user())
            ->log('Impreso Listado de Pre-Factura.');

        $name = "PreFact_" . date('YmdHis') . ".pdf";
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.factura.index_pre_facturas_pdf', [
            'facturas' => $facturas,
            'name' => $name
        ]);
        $pdf->save($name);
        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name";
        $this->iframeContainerClass = 'show';
    }
}
