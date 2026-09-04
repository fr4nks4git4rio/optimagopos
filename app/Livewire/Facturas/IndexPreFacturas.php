<?php

namespace App\Livewire\Facturas;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Moneda;
use App\Services\Timbrado\Facturador;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPreFacturas extends Component
{
    use WithPagination;

    public $page;
    public $perPage;
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
        $this->page = $this->page ?? 1;
        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? null;
        $this->order = $this->order ?? 'desc';
        $this->sort = $this->sort ?? __('site.invoices.index.date');
        $this->fechaInicio = $this->fechaInicio ?? null;
        $this->fechaFin = $this->fechaFin ?? null;
        $this->cliente = $this->cliente ?? null;
        $this->estado = $this->estado ?? __('site.common.all');
        $this->moneda = $this->moneda ?? __('site.common.all');
        $this->importe = $this->importe ?? null;
        $this->sorts = [
            __('site.invoices.index.date'),
            __('site.invoices.index.receiver'),
            __('site.invoices.index.status'),
            __('site.invoices.index.currency'),
            __('site.invoices.index.subtotal'),
            __('site.invoices.index.iva'),
            __('site.invoices.index.total')
        ];
        $this->perPages = [10, 25, 50, 100];
        $this->estados = ['PRECAPTURADA', 'CAPTURADA'];
        $this->monedas = Moneda::all()->pluck('acronimo');
        //        $this->filters = ['Activos', 'Inactivos', 'Todos'];
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function updated($field)
    {
        if (in_array($field, [
            'search',
            'perPage',
            'sort',
            'fechaInicio',
            'fechaFin',
            'cliente',
            'estado',
            'moneda',
            'importe'
        ])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $records = $this->query();

        $currentPage = $this->getPage();
        $total = $records->count();
        $currentItems = $records->forPage($currentPage, $this->perPage)->values();

        $facturas = new LengthAwarePaginator($currentItems, $total, $this->perPage, $currentPage, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        return view('livewire.facturas.index-pre-facturas', [
            'facturas' => $facturas,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAny', [Factura::class])) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }

        if ($this->cliente) {
            $cliente = Cliente::find($this->cliente);
            $cliente = Cliente::decryptInfo($cliente);
            $this->dispatch('set-data-cliente', ['data' => [['id' => $cliente->id, 'text' => $cliente->nombre_comercial]], 'term' => '', 'value' => $this->cliente]);
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
                'factura.es_complemento',
                DB::raw("(SELECT GROUP_CONCAT(fc.descripcion SEPARATOR '
                ') FROM tb_factura_conceptos as fc WHERE fc.factura_id = factura.id) as conceptos")
            )
            ->leftJoin('tb_clientes as cliente', 'factura.cliente_id', '=', 'cliente.id')
            ->leftJoin('tb_sucursales as propietario', 'factura.propietario_id', '=', 'propietario.id')
            ->distinct('factura.id')
            ->where('factura.user_id', '>', 0)
            ->where('del_sistema', 0);
        if ($this->fechaInicio) {
            $query->where('factura.fecha_emision', '>=', $this->fechaInicio);
        }
        if ($this->fechaFin) {
            $query->where('factura.fecha_emision', '<=', $this->fechaFin);
        }
        if ($this->cliente) {
            $query->where('factura.cliente_id', $this->cliente);
        }
        if ($this->estado && $this->estado != __('site.common.all')) {
            $query->where('factura.estado', $this->estado);
        } else {
            $query->whereIn('factura.estado', ['PRECAPTURADA', 'CAPTURADA']);
        }
        if ($this->moneda && $this->moneda != __('site.common.all')) {
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
            case __('site.invoices.index.date'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('fecha_certificacion_sort', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('fecha_certificacion_sort', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index.receiver'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('receptor', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('receptor', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index.status'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('estado', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('estado', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index.currency'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('moneda', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('moneda', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index.subtotal'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('subtotal', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('subtotal', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index.iva'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('iva', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('iva', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index.total'):
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
        $cliente_publico_general = DB::table('tb_clientes')->where('rfc', 'XAXX010101000')->get()->first();
        if (!$cliente_publico_general) {
            DB::table('tb_clientes')->insert([
                'nombre_comercial' => Crypt::encrypt('Público General'),
                'razon_social' => Crypt::encrypt('VENTA A PUBLICO GENERAL'),
                'rfc' => 'XAXX010101000',
                'es_cliente' => 1,
                'regimen_fiscal_id' => 1
            ]);
        }
        return redirect()->route('cliente.pre-facturas.save');
    }

    public function timbrar($id)
    {
        $factura = Factura::find($id);
        $folio_interno = Factura::internalSheetGenerator($factura->serie_id, modo_facturacion($factura->propietario_id) == 1);
        $facturador = new Facturador($factura->propietario);
        $res = $facturador->timbrarFactura($id, $folio_interno);
        if ($res['success']) {
            $this->dispatch('show-toast', __('site.invoices.index.stamp_invoice_successfully'), 'success');
        } else {
            $this->dispatch('show-toast', pretty_message($res['message'], 'danger'), 'danger');
        }
    }

    public function showPdf($id)
    {
        $name = Factura::generateFacturaPdf($id, true);
        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name?" . time();
        $this->dispatch('show-sub-modal', 'pdf-prefacturas');
    }

    public function imprimirFacturas()
    {
        $facturas = $this->query();

        activity(__('site.invoices.index.print_log_name'))
            ->causedBy(auth()->user())
            ->log(__('site.invoices.index.print_log_detail'));

        $name = "PreFact_" . date('YmdHis') . ".pdf";
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.factura.index_pre_facturas_pdf', [
            'facturas' => $facturas,
            'name' => $name,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'cliente' => $this->cliente ? Crypt::decrypt(Cliente::find($this->cliente)->nombre_comercial) : '',
            'estado' => $this->estado,
            'moneda' => $this->moneda,
            'importe' => $this->importe,
        ]);
        $pdf->save($name);
        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name";

        $this->dispatch('show-sub-modal', 'pdf-prefacturas');
    }
}
