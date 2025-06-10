<?php

namespace App\Http\Livewire\Facturas;

use App\Exports\FacturaEmitidaExport;
use App\Http\Libraries\Pdf;
use App\Models\Facturador;
use App\Models\Cliente;
use App\Models\Factura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
    public $search;
    public $sort = 'Fecha';
    public $sorts;
    public $fechaInicio;
    public $fechaFin;
    public $cliente;
    public $estado = 'Todos';
    public $estados = ['Todos', 'TIMBRADA', 'CANCELADA'];
    public $folioInterno;
    public $moneda;
    public $monedas = ['Todos', 'MXN', 'USD'];
    public $importe;
    public $iframeContainerClass = '';
    public $iframeSrc = '';
    //    public $filter = 'Activos';
    //    public $filters;

    protected $queryString = ['search', 'perPage', 'sort', 'fechaInicio', 'fechaFin', 'cliente', 'estado', 'moneda', 'importe'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->sorts = ['Fecha', 'F. Int.', 'Receptor', 'Estado', 'Moneda', 'Subtotal', 'IVA', 'Ret. IVA', 'Total'];
        $this->perPages = [10, 25, 50, 100];
        //        $this->filters = ['Activos', 'Inactivos', 'Todos'];
    }

    public function render()
    {
        $facturas = $this->query();
        $total = $facturas->count();
        $records = $facturas->forPage($this->page, $this->perPage);
        $facturas = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.facturas.index-almacen', [
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
                DB::raw('DATE_FORMAT(factura.fecha_emision, "%d/%m/%Y") as fecha_emision_str'),
                DB::raw('DATE_FORMAT(factura.fecha_certificacion, "%Y-%m-%d %H:%i:%s") as fecha_certificacion_sort'),
                DB::raw('DATE_FORMAT(factura.fecha_certificacion, "%d/%m/%Y") as fecha_certificacion'),
                'factura.uuid',
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
                DB::raw("(SELECT GROUP_CONCAT(fc.descripcion SEPARATOR '
                ') FROM tb_factura_conceptos as fc WHERE fc.factura_id = factura.id) as conceptos"),
                'mc.descripcion as motivo_cancelacion'
            )
            ->leftJoin('tb_clientes as cliente', 'factura.cliente_id', '=', 'cliente.id')
            ->leftJoin('tb_clientes as propietario', 'factura.propietario_id', '=', 'propietario.id')
            ->leftJoin('tb_motivos_cancelacion_factura as mc', 'factura.motivo_cancelacion_id', 'mc.id')
            ->whereIn('factura.estado', ['TIMBRADA', 'CANCELADA'])
            ->distinct('factura.id');

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
        }
        if ($this->folioInterno) {
            $query->where('factura.folio_interno', 'like', "%$this->folioInterno%");
        }
        if ($this->moneda && $this->moneda != 'Todos') {
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
                $final_records = $final_records->sortByDesc('fecha_certificacion_sort', SORT_NATURAL)->values();
                break;
            case 'Receptor':
                $final_records = $final_records->sortBy('receptor', SORT_NATURAL)->values();
                break;
            case 'Estado':
                $final_records = $final_records->sortBy('estado', SORT_NATURAL)->values();
                break;
            case 'Moneda':
                $final_records = $final_records->sortBy('moneda', SORT_NATURAL)->values();
                break;
            case 'Subtotal':
                $final_records = $final_records->sortBy('subtotal', SORT_NATURAL)->values();
                break;
            case 'IVA':
                $final_records = $final_records->sortBy('iva', SORT_NATURAL)->values();
                break;
            case 'Total':
                $final_records = $final_records->sortBy('total', SORT_NATURAL)->values();
                break;
        }

        return $final_records;
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
            $this->emit('show-toast', 'No se encontró el archivo XML', 'danger');
    }

    public function imprimirFacturas()
    {
        $facturas = $this->query();

        activity('Almacén de Facturas')
            ->causedBy(auth()->user())
            ->log('Impreso Listado de Almacén de Factura.');

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

        return (new FacturaEmitidaExport($facturas))->download("Facturas Emitidas.xls");
    }
}
