<?php

namespace App\Http\Livewire\FacturasSistema;

use App\Exports\FacturaEmitidaExport;
use App\Http\Libraries\Pdf;
use App\Models\Facturador;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Moneda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class IndexAlmacen extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $perPages;
    public $order;
    public $search;
    public $sort;
    public $sorts;
    public $fechaInicio;
    public $fechaFin;
    public $cliente;
    public $estado;
    public $estados = [];
    public $folioInterno;
    public $moneda;
    public $monedas = [];
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

        $this->monedas = Moneda::pluck('acronimo')->toArray();
        $this->monedas = Arr::prepend($this->monedas, __('site.common.all'));

        $this->estados = [__('site.common.all'), __('site.statuses.invoices.TIMBRADA'), __('site.statuses.invoices.CANCELADA')];
        $this->sorts = [__('site.invoices.index_storage.date'), __('site.invoices.index_storage.f_int'), __('site.invoices.index_storage.type'), __('site.invoices.index_storage.receiver'), __('site.invoices.index_storage.status'), __('site.invoices.index_storage.currency'), __('site.invoices.index_storage.subtotal'), __('site.invoices.index_storage.iva'), __('site.invoices.index_storage.total')];
        $this->perPages = [10, 25, 50, 100];
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
        return view('livewire.facturas-sistema.index-almacen', [
            'facturas' => $facturas,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAnyFacturaSistema', [Factura::class])) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }

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
                DB::raw('DATE_FORMAT(factura.fecha_emision, "%d/%m/%Y") as fecha_emision_str'),
                DB::raw('DATE_FORMAT(factura.fecha_certificacion, "%Y-%m-%d %H:%i:%s") as fecha_certificacion_sort'),
                DB::raw('DATE_FORMAT(factura.fecha_certificacion, "%d/%m/%Y") as fecha_certificacion'),
                'factura.uuid',
                DB::raw("IF(factura.es_complemento = 1, 'COMP.', IF(factura.es_nota_credito = 1, 'NOT.CRE.', 'FACT.')) as tipo"),
                'factura.folio_interno',
                'cliente.razon_social as receptor',
                'cliente.rfc as rfc_receptor',
                'propietario.razon_social as emisor',
                'propietario.rfc as rfc_emisor',
                'factura.estado',
                'factura.moneda',
                'factura.subtotal',
                'factura.iva',
                'factura.total',
                DB::raw("IF(factura.suscripcion_id IS NOT NULL, IF(paquete.nombre IS NOT NULL, paquete.nombre, 'CUSTOM'), '') as paquete"),
                DB::raw("(SELECT GROUP_CONCAT(fc.descripcion SEPARATOR '
                ') FROM tb_factura_conceptos as fc WHERE fc.factura_id = factura.id) as conceptos"),
                'mc.descripcion as motivo_cancelacion'
            )
            ->leftJoin('tb_clientes as cliente', 'factura.cliente_id', '=', 'cliente.id')
            ->leftJoin('tb_clientes as propietario', 'factura.propietario_id', '=', 'propietario.id')
            ->leftJoin('tb_motivos_cancelacion_factura as mc', 'factura.motivo_cancelacion_id', 'mc.id')
            ->leftJoin('tb_suscripciones as suscripcion', 'factura.suscripcion_id', '=', 'suscripcion.id')
            ->leftJoin('tb_paquetes as paquete', 'suscripcion.paquete_id', '=', 'paquete.id')
            ->whereIn('factura.estado', ['TIMBRADA', 'CANCELADA'])
            ->distinct('factura.id')
            ->where('del_sistema', 1);

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
            $folio_interno = strtoupper($record->folio_interno);
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
            case 'F. Int.':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('folio_interno', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('folio_interno', SORT_NATURAL)->values();
                break;
            case 'Tipo':
                if ($this->order == 'asc')
                    $final_records = $final_records->sortBy('tipo', SORT_NATURAL)->values();
                else
                    $final_records = $final_records->sortByDesc('tipo', SORT_NATURAL)->values();
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

    public function showPdf($id)
    {
        $factura = Factura::find($id);
        $name = Factura::generateFacturaPdf($id, true);
        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name?" . time();
        $this->iframeContainerClass = 'show';
    }

    public function descargarXml($id)
    {
        $factura = Factura::find($id);
        if ($factura->direccion_xml && Storage::disk('public')->exists($factura->direccion_xml))
            return Storage::download("public/$factura->direccion_xml");
        else
            $this->emit('show-toast', __('site.invoices.index_storage.no_xml_file_found'), 'danger');
    }

    public function imprimirFacturas()
    {
        $facturas = $this->query();

        activity(__('site.invoices.index_storage.printing_log_name'))
            ->causedBy(auth()->user())
            ->log(__('site.invoices.index_storage.printing_log_detail'));

        $name = "AlmFAct_" . date('YmdHis') . ".pdf";
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.factura.index_almacen_pdf', [
            'facturas' => $facturas,
            'name' => $name
        ]);
        $pdf->save($name);
        $this->iframeSrc = \Illuminate\Support\Facades\Request::root() . "/$name";
        $this->iframeContainerClass = 'show';
    }

    public function exportarExcelFacturas()
    {
        $facturas = $this->query();

        return (new FacturaEmitidaExport($facturas))->download(__('site.invoices.index_storage.download_xml_name') . ".xls");
    }
}
