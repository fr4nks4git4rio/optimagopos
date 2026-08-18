<?php

namespace App\Livewire\Reportes;

use App\Exports\ReporteIngresosExport;
use App\Http\Libraries\Pdf;
use App\Models\Factura;
use App\Models\Ingreso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use Livewire\WithPagination;

class Ingresos extends Component
{
    use WithPagination;

    public $page;
    public $perPage;
    public $perPages = [];
    public $order;
    public $sort;
    public $sorts = [];
    public $fechaInicio;
    public $fechaFin;
    public $cliente;
    public $moneda;
    public $importe;
    public $monedas = ['MXN', 'USD'];

    public $iframeContainerClass = '';
    public $iframeSrc = '';

    protected $queryString = ['perPage', 'sort', 'order', 'fechaInicio', 'fechaFin', 'cliente', 'moneda', 'importe'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->page ??= 1;
        $this->perPage ??= 10;
        $this->perPages = [10, 25, 50, 100];
        $this->sort ??= __('site.reports.income.date');
        $this->sorts = [
            __('site.reports.income.date'),
            __('site.reports.income.internal_folio'),
            __('site.reports.income.client'),
            __('site.reports.income.uuid'),
            __('site.reports.income.currency'),
            __('site.reports.income.import')
        ];
    }

    public function updated($field)
    {
        if (in_array($field, ['perPage', 'sort', 'order', 'fechaInicio', 'fechaFin', 'cliente', 'moneda', 'importe']))
            $this->resetPage();
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        return view('livewire.reportes.ingresos', [
            'ingresos' => $this->query()->paginate($this->perPage),
        ]);
    }

    public function query()
    {
        $query = DB::table('tb_ingresos_facturas as ingreso_factura')
            ->select(
                'ingreso.id as id',
                'ingreso.fecha',
                DB::raw("DATE_FORMAT(ingreso.fecha, '%d/%m/%Y') as fecha_str"),
                DB::raw("IF(ingreso_factura.nota_credito_id IS NULL, factura.folio_interno, nota_credito.folio_interno) as folio_interno"),
                'cliente.razon_social as razon_social',
                'factura.uuid as uuid',
                'ingreso_factura.monto as monto',
                'ingreso_factura.moneda as moneda',
                'cliente.id as cliente_id',
                'factura.id as factura_id'
            )
            ->leftJoin('tb_ingresos as ingreso', 'ingreso.id', '=', 'ingreso_factura.ingreso_id')
            ->leftJoin('tb_facturas as factura', 'factura.id', '=', 'ingreso_factura.factura_id')
            ->leftJoin('tb_facturas as nota_credito', 'factura.id', '=', 'ingreso_factura.nota_credito_id')
            ->leftJoin('tb_clientes as cliente', 'factura.cliente_id', '=', 'cliente.id');

        if (!$this->fechaInicio && !$this->fechaFin && !$this->cliente && !$this->moneda && !$this->importe)
            $query->where('ingreso.id', 0);

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereDate('fecha', '>=', $this->fechaInicio);
            $query->whereDate('fecha', '<=', $this->fechaFin);
        } elseif ($this->fechaInicio && !$this->fechaFin) {
            $query->whereDate('fecha', '>=', $this->fechaInicio);
        } elseif (!$this->fechaInicio && $this->fechaFin) {
            $query->whereDate('fecha', '<=', $this->fechaFin);
        }

        if ($this->cliente && $this->cliente != -1) {
            $query->where('cliente.id', $this->cliente);
        }

        if ($this->moneda) {
            $query->where('ingreso_factura.moneda', $this->moneda);
        }

        if ($this->importe) {
            $query->where('monto', 'like', '%' . $this->importe . '%');
        }

        switch ($this->sort) {
            case __('site.reports.income.date'):
                if ($this->order == 'desc')
                    $query->orderByDesc('fecha');
                else
                    $query->orderBy('fecha');
                break;
            case __('site.reports.income.internal_folio'):
                if ($this->order == 'desc')
                    $query->orderByDesc('folio_interno')
                        ->orderByRaw("LENGTH(folio_interno) DESC");
                else
                    $query->orderBy('folio_interno', 'desc')
                        ->orderByRaw("LENGTH(folio_interno) DESC");
                break;
            case __('site.reports.income.client'):
                if ($this->order == 'desc')
                    $query->orderByDesc('razon_social');
                else
                    $query->orderBy('razon_social');
                break;
            case __('site.reports.income.uuid'):
                if ($this->order == 'desc')
                    $query->orderByDesc('uuid');
                else
                    $query->orderBy('uuid');
                break;
            case __('site.reports.income.currency'):
                if ($this->order == 'desc')
                    $query->orderByDesc('moneda');
                else
                    $query->orderBy('moneda');
                break;
            case __('site.reports.income.import'):
                if ($this->order == 'desc')
                    $query->orderByDesc('monto');
                else
                    $query->orderBy('monto');
                break;
        }

        return $query;
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }


    public function imprimirFactura($id)
    {
        $factura = Factura::find($id);
        if ($factura->es_complemento)
            $name = Factura::generateComplementoPdf($id, true);
        else
            $name = Factura::generatePdf($id, true);
        $this->iframeSrc = Request::root() . "/$name?" . time();
        $this->dispatch('show-sub-modal', 'pdf-ingresos');
    }

    public function imprimirIngresoPdf($id)
    {
        $name = Ingreso::imprimirIngresoPdf($id, true);

        $this->iframeSrc = Request::root() . "/$name";
        $this->dispatch('show-sub-modal', 'pdf-ingresos');
    }

    public function imprimirListadoIngresos()
    {
        $name = __('site.reports.income.title');
        $ingresos = $this->query()->get();
        $pdf = new Pdf();
        $pdf->AddPage('L');
        $pdf->SetMargins(5, 10);
        $pdf->SetFont('arial', 'B', 12);

        //    $pdf->Image('img/transparent.png', 1, 1, 1, 1);
        $pdf->Cell(0, 10, utf8_decode($name), 0, 1, 'C');
        $pdf->Ln(10);

        $col1 = $pdf->pageWidth() * 0.10;
        $col2 = $pdf->pageWidth() * 0.40;
        $col3 = $pdf->pageWidth() * 0.20;
        $pdf->SetFontSize(10);
        $pdf->Cell($col1, 8, __('site.reports.income.date'), 1, 0, 'C');
        $pdf->Cell($col1, 8, __('site.reports.income.internal_folio'), 1, 0, 'C');
        $pdf->Cell($col2, 8, __('site.reports.income.client'), 1, 0, 'C');
        $pdf->Cell($col3, 8, __('site.reports.income.uuid'), 1, 0, 'C');
        $pdf->Cell($col1, 8, __('site.reports.income.currency'), 1, 0, 'C');
        $pdf->Cell($col1, 8, __('site.reports.income.import'), 1, 1, 'C');

        $pdf->SetFont('arial', '', 8);
        $total_importe_mxn = 0;
        $total_importe_usd = 0;
        foreach ($ingresos as $ingreso) {
            if ($ingreso->moneda === 'USD')
                $total_importe_usd += $ingreso->monto;
            elseif ($ingreso->moneda === 'MXN')
                $total_importe_mxn += $ingreso->monto;

            $pdf->SetX(5 + $col1 * 2);
            $y_ini = $pdf->GetY();
            $razon_social = $ingreso->razon_social ? Crypt::decrypt($ingreso->razon_social) : '';
            $pdf->MultiCell($col2, 6, utf8_decode($razon_social), 1, 'C');
            $height = $pdf->GetY() - $y_ini;
            $pdf->SetY($y_ini);
            $pdf->Cell($col1, $height, $ingreso->fecha_str, 1, 0, 'C');
            $pdf->Cell($col1, $height, $ingreso->folio_interno, 1, 0, 'C');
            $pdf->SetX(5 + $col1 * 2 + $col2);
            $pdf->Cell($col3, $height, $ingreso->uuid, 1, 0, 'C');
            $pdf->Cell($col1, $height, $ingreso->moneda, 1, 0, 'C');
            $pdf->Cell($col1, $height, number_format($ingreso->monto, 2), 1, 1, 'C');

            if ($pdf->GetPageHeight() - $pdf->GetY() <= 30) {
                $pdf->AddPage('L');
                $pdf->SetFont('arial', '', 8);
            }
        }
        $pdf->SetFont('arial', 'B', 8);
        $pdf->Cell($col1 * 2 + $col2 + $col3, 12, 'Totales:', 1, 0, 'R');
        $pdf->Cell($col1, 6, 'MXN:', 1, 0, 'C');
        $pdf->Cell($col1, 6, number_format($total_importe_mxn, 2), 1, 1, 'C');
        $pdf->SetX($col1 * 2 + $col2 + $col3 + 5);
        $pdf->Cell($col1, 6, 'USD:', 1, 0, 'C');
        $pdf->Cell($col1, 6, number_format($total_importe_usd, 2), 1, 1, 'C');

        $pdf->Output('F', "$name.pdf");

        $this->iframeSrc = Request::root() . "/$name.pdf?" . now()->timestamp;
        $this->dispatch('show-sub-modal', 'pdf-ingresos');
    }

    public function exportarExcelListadoIngresos()
    {
        $name = __('site.reports.income.title');
        $ingresos = $this->query()->get();

        return (new ReporteIngresosExport($name, $ingresos))->download("$name.xls");
    }
}
