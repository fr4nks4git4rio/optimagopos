<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\ReporteIngresosExport;
use App\Http\Libraries\Pdf;
use App\Models\Factura;
use App\Models\Ingreso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use Livewire\WithPagination;

class Ingresos extends Component
{
    use WithPagination;

    public $perPage;
    public $perPages = [];
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

    protected $queryString = ['perPage', 'sort', 'fechaInicio', 'fechaFin', 'cliente', 'moneda', 'importe'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->perPage = 10;
        $this->perPages = [10, 25, 50, 100];
        $this->sort = 'Fecha';
        $this->sorts = ['Fecha', 'Folio Interno', 'Cliente', 'Folio UUID', 'Moneda', 'Importe'];
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
            case 'Fecha':
                $query->orderBy('fecha', 'desc');
                break;
            case 'Folio Interno':
                $query->orderBy('folio_interno', 'desc')
                    ->orderByRaw("LENGTH(folio_interno) DESC");
                break;
            case 'Cliente':
                $query->orderBy('razon_social');
                break;
            case 'Folio UUID':
                $query->orderBy('uuid');
                break;
            case 'Moneda':
                $query->orderBy('moneda');
                break;
            case 'Importe':
                $query->orderBy('monto');
                break;
        }

        return $query;
    }

    public function imprimirFactura($id)
    {
        $factura = Factura::find($id);
        if ($factura->es_complemento)
            $name = Factura::generateComplementoPdf($id, true);
        else
            $name = Factura::generatePdf($id, true);
        $this->iframeSrc = Request::root() . "/$name?" . time();
        $this->iframeContainerClass = 'show';
    }

    public function imprimirIngresoPdf($id)
    {
        $name = Ingreso::imprimirIngresoPdf($id, true);

        $this->iframeSrc = Request::root() . "/$name";
        $this->iframeContainerClass = 'show';
    }

    public function imprimirListadoIngresos()
    {
        $ingresos = $this->query()->get();
        $pdf = new Pdf();
        $pdf->AddPage('L');
        $pdf->SetMargins(5, 10);
        $pdf->SetFont('arial', 'B', 12);

        //    $pdf->Image('img/transparent.png', 1, 1, 1, 1);
        $pdf->Cell(0, 10, utf8_decode('Reporte de Ingresos'), 0, 1, 'C');
        $pdf->Ln(10);

        $col1 = $pdf->pageWidth() * 0.10;
        $col2 = $pdf->pageWidth() * 0.40;
        $col3 = $pdf->pageWidth() * 0.20;
        $pdf->SetFontSize(10);
        $pdf->Cell($col1, 8, 'Fecha', 1, 0, 'C');
        $pdf->Cell($col1, 8, 'Folio Int.', 1, 0, 'C');
        $pdf->Cell($col2, 8, 'Cliente', 1, 0, 'C');
        $pdf->Cell($col3, 8, 'Folio UUID', 1, 0, 'C');
        $pdf->Cell($col1, 8, 'Moneda', 1, 0, 'C');
        $pdf->Cell($col1, 8, 'Importe', 1, 1, 'C');

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
            $pdf->MultiCell($col2, 6, utf8_decode($ingreso->razon_social), 1, 'C');
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

        $pdf->Output('F', 'Reporte de Ingresos.pdf');

        $this->iframeSrc = Request::root() . '/Reporte de Ingresos.pdf';
        $this->iframeContainerClass = 'show';
    }

    public function exportarExcelListadoIngresos()
    {
        $ingresos = $this->query()->get();

        return (new ReporteIngresosExport($ingresos))->download("Reporte de Ingresos.xls");
    }
}
