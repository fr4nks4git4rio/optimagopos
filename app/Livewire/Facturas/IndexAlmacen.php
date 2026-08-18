<?php

namespace App\Livewire\Facturas;

use App\Exports\AlmacenFacturaExport;
use App\Models\Cliente;
use App\Models\Factura;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class IndexAlmacen extends Component
{
    use WithPagination;

    public $page;
    public $perPage;
    public $perPages;
    public $order;
    public $search;
    public $sort = 'Fecha';
    public $sorts;
    public $fechaInicio;
    public $fechaFin;
    public $cliente;
    public $estado;
    public $estados = ['TIMBRADA', 'CANCELADA'];
    public $folioInterno;
    public $moneda;
    public $monedas = ['MXN', 'USD'];
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
        'importe' => ['except' => null],
        'folioInterno' => ['except' => null]
    ];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->perPage = $this->perPage ?? 10;
        $this->order = $this->order ?? 'desc';
        $this->search = $this->search ?? null;
        $this->sort = $this->sort ?? __('site.invoices.index_storage.date');
        $this->fechaInicio = $this->fechaInicio ?? null;
        $this->fechaFin = $this->fechaFin ?? null;
        $this->cliente = $this->cliente ?? null;
        $this->estado = $this->estado ?? __('site.common.all');
        $this->folioInterno = $this->folioInterno ?? null;
        $this->moneda = $this->moneda ?? __('site.common.all');
        $this->importe = $this->importe ?? null;

        $this->sorts = [
            __('site.invoices.index_storage.date'),
            __('site.invoices.index_storage.f_int'),
            __('site.invoices.index_storage.receiver'),
            __('site.invoices.index_storage.status'),
            __('site.invoices.index_storage.currency'),
            __('site.invoices.index_storage.subtotal'),
            __('site.invoices.index_storage.iva'),
            __('site.invoices.index_storage.total')
        ];
        $this->perPages = [10, 25, 50, 100];
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
            'importe',
            'folioInterno'
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
        return view('livewire.facturas.index-almacen', [
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
                DB::raw('DATE_FORMAT(factura.fecha_emision, "%d/%m/%Y") as fecha_emision_str'),
                DB::raw('DATE_FORMAT(factura.fecha_certificacion, "%Y-%m-%d %H:%i:%s") as fecha_certificacion_sort'),
                DB::raw('DATE_FORMAT(factura.fecha_certificacion, "%d/%m/%Y") as fecha_certificacion'),
                'factura.es_complemento',
                'factura.es_nota_credito',
                'factura.uuid',
                'factura.folio_interno',
                DB::raw("IFNULL(cliente.razon_social, cliente.nombre_comercial) as receptor"),
                'cliente.rfc as rfc_receptor',
                DB::raw("IFNULL(propietario.razon_social, propietario.nombre_comercial) as emisor"),
                'propietario.rfc as rfc_emisor',
                'factura.estado',
                'factura.moneda',
                'factura.subtotal',
                'factura.iva',
                'factura.total',
                DB::raw("(SELECT GROUP_CONCAT(fc.descripcion SEPARATOR '
                ') FROM tb_factura_conceptos as fc WHERE fc.factura_id = factura.id) as conceptos"),
                'mc.descripcion as motivo_cancelacion',
                DB::raw("'' as tipo")
            )
            ->leftJoin('tb_clientes as cliente', 'factura.cliente_id', '=', 'cliente.id')
            ->leftJoin('tb_sucursales as propietario', 'factura.propietario_id', '=', 'propietario.id')
            ->leftJoin('tb_motivos_cancelacion_factura as mc', 'factura.motivo_cancelacion_id', 'mc.id')
            ->whereIn('factura.estado', ['TIMBRADA', 'CANCELADA'])
            ->distinct('factura.id')
            ->where('del_sistema', 0);

        if ($this->fechaInicio) {
            $query->where('factura.fecha_certificacion', '>=', $this->fechaInicio);
        }
        if ($this->fechaFin) {
            $query->where('factura.fecha_certificacion', '<=', $this->fechaFin);
        }
        if ($this->cliente) {
            $query->where('factura.cliente_id', $this->cliente);
        }
        if ($this->estado && $this->estado != __('site.common.all')) {
            $query->where('factura.estado', $this->estado);
        }
        if ($this->folioInterno) {
            $query->where('factura.folio_interno', 'like', "%$this->folioInterno%");
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
            if ($record->es_complemento)
                $record->tipo = __('site.common.complement');
            elseif ($record->es_nota_credito)
                $record->tipo = __('site.common.credit_note');
            else
                $record->tipo = __('site.common.invoice');
            $folio_interno = strtoupper($record->folio_interno);
            $record->emisor = strtoupper(Crypt::decrypt($record->emisor));
            $record->receptor = strtoupper(Crypt::decrypt($record->receptor));
            if (
                !$this->search
                || str_contains($record->fecha_certificacion, $this->search)
                || str_contains($folio_interno, $this->search)
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
            case __('site.invoices.index_storage.date'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('fecha_certificacion_sort', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('fecha_certificacion_sort', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index_storage.f_int'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('folio_interno', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('folio_interno', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index_storage.receiver'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('receptor', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('receptor', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index_storage.status'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('estado', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('estado', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index_storage.currency'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('moneda', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('moneda', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index_storage.subtotal'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('subtotal', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('subtotal', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index_storage.iva'):
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('iva', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('iva', SORT_NATURAL)->values();
                break;
            case __('site.invoices.index_storage.total'):
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

    public function showPdf($id)
    {
        $name = Factura::generateFacturaPdf($id, true);
        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name?" . time();
        $this->dispatch('show-sub-modal', 'pdf-almacen-facturas');
    }

    public function descargarXml($id)
    {
        $factura = Factura::find($id);
        if ($factura->direccion_xml && Storage::disk('public')->exists($factura->direccion_xml))
            return Storage::download("public/$factura->direccion_xml");
        else
            $this->dispatch('show-toast', __('site.common.file_not_found'), 'danger');
    }

    public function imprimirFacturas()
    {
        $facturas = $this->query();

        $name = __('site.invoices.index_storage.print_log_name');
        activity($name)
            ->causedBy(auth()->user())
            ->log(__('site.invoices.index_storage.print_log_detail'));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.factura.index_almacen_pdf', [
            'facturas' => $facturas,
            'name' => $name
        ]);
        $pdf->save("$name.pdf");
        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name.pdf?" . now()->timestamp;
        $this->dispatch('show-sub-modal', 'pdf-almacen-facturas');
    }

    public function exportarExcelFacturas()
    {
        $facturas = $this->query();

        activity(__('site.invoices.index_storage.print_log_name'))
            ->causedBy(auth()->user())
            ->log(__('site.invoices.index_storage.exporting_log_detail'));

        $name = __('site.invoices.index_storage.print_log_name');

        return (new AlmacenFacturaExport($name, $facturas))->download("$name.xls");
    }
}
