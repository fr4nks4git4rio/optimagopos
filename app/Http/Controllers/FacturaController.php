<?php

namespace App\Http\Controllers;

use App\Http\Libraries\Pdf;
use App\Models\Factura;
use App\Models\FacturaConcepto;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Str;

class FacturaController extends Controller
{
    private function query(Request $request)
    {
        $query = DB::table('tb_facturas as factura')
            ->selectRaw("factura.id,
          DATE_FORMAT(factura.fecha_certificacion, '%d/%m/%Y') as fecha_certificacion,
          DATE_FORMAT(factura.fecha_emision, '%d/%m/%Y') as fecha_emision,
          factura.modo_prueba_cfdi,
          factura.folio_interno,
          factura.direccion_xml,
          factura.es_nota_credito,
          factura.es_complemento,
          factura.porciento_iva,
          factura.moneda as moneda,
          factura.estado,
          factura.cliente_id,
          cliente.razon_social as cliente_receptor,
          cliente.correo as correo_cliente,
          factura.subtotal,
          factura.total,
          'FACTURA' as tipo,
          IFNULL((SELECT SUM(ing_f.monto_moneda_original + IFNULL(nc.total, 0)) from tb_ingresos_facturas as ing_f left join tb_facturas as nc on nc.id = ing_f.nota_credito_id where ing_f.factura_id = factura.id),0) as monto_ingresado,
          (factura.total - IFNULL((SELECT SUM(ing_f.monto_moneda_original + IFNULL(nc.total, 0)) from tb_ingresos_facturas as ing_f left join tb_facturas as nc on nc.id = ing_f.nota_credito_id where ing_f.factura_id = factura.id), 0)) as pendiente_ingresar,
          0 as seleccionado")
            ->where('factura.estado', 'TIMBRADA')
            ->where('factura.es_nota_credito', 0)
            ->where('factura.es_complemento', 0)
            // ->where('factura.modo_prueba_cfdi', NULL)
            ->where('factura.del_sistema', 1)
            ->leftJoin('tb_clientes as cliente', 'factura.cliente_id', '=', 'cliente.id')
            ->leftJoin('tb_factura_conceptos as f_c', 'f_c.factura_id', '=', 'f_c.id');

        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->whereRaw("DATE(factura.fecha_certificacion) >= '" . $request->fecha_inicio . "' and DATE(factura.fecha_certificacion) <= '" . $request->fecha_fin . "'");
        } elseif ($request->fecha_inicio && !$request->fecha_fin) {
            $query->whereRaw("DATE(factura.fecha_certificacion) >= '" . $request->fecha_inicio . "'");
        } elseif (!$request->fecha_inicio && $request->fecha_fin) {
            $query->whereRaw("DATE(factura.fecha_certificacion) <= '" . $request->fecha_fin . "'");
        }

        if ($request->cliente && $request->cliente != -1) {
            $query->where('factura.cliente_id', $request->cliente);
        }

        if ($request->folio_interno) {
            $query->where('factura.folio_interno', 'like', "%$request->folio_interno%");
        }

        if ($request->importe) {
            $query->whereRaw("ROUND(factura.total) = '" . round($request->importe) . "'");;
        }

        if ($request->moneda && $request->moneda != -1) {
            $query->where('factura.moneda', $request->moneda);
        }

        switch ($request->sort) {
            case 'F. Int.':
                $query->orderByRaw('LENGTH(factura.folio_interno) DESC')->orderBy('factura.folio_interno', 'desc');
                break;
            case 'Fecha Factura':
                $query->orderBy('factura.fecha_emision', 'desc');
                break;
            case 'Receptor':
                $query->orderBy('cliente.razon_social');
                break;
            case 'Tipo':
                $query->orderBy('factura.es_complemento', 'desc')
                    ->orderBy('factura.es_nota_credito', 'desc');
                break;
            case 'Moneda':
                $query->orderBy('factura.moneda');
                break;
            case 'Total':
                $query->orderByRaw("LENGTH(factura.total) DESC")->orderBy('factura.total');
                break;
            case 'Pendiente':
                $query->orderByRaw("LENGTH(factura.pendiente_ingresar) DESC")->orderBy('factura.pendiente_ingresar');
                break;
        }
        return $query;
    }

    public function loadCuentasCobrar(Request $request)
    {

        $query = $this->query($request)->having('pendiente_ingresar', '>', 0);

        $records = $query->get()->each(function ($factura) {
            $factura->cliente_receptor = Crypt::decrypt($factura->cliente_receptor);
            $factura->correo_cliente = Crypt::decrypt($factura->correo_cliente);
        });

        $page = $request->page ?: 1;
        $perPage = $request->perPage ?: 10;
        $total = $records->count();
        $records = $records->forPage($page, $perPage);
        $facturas = new LengthAwarePaginator($records, $total, $perPage, $page);

        return ['success' => true, 'data' => $facturas];
    }

    public function imprimirListadoCuentasCobrar(Request $request)
    {
        $facturas = $this->query($request)->get();
        foreach ($facturas as &$factura) {
            $fact = Factura::find($factura->id);

            $monto_ingresado = 0;
            $fact->ingresos->each(function ($ingreso) use (&$monto_ingresado) {
                $monto_ingresado += $ingreso->monto_moneda_original;
            });
            $factura->monto_ingresado = $monto_ingresado;
            $factura->pendiente_ingresar = $factura->total - $factura->monto_ingresado;

            $factura->cliente_receptor = Crypt::decrypt($factura->cliente_receptor);
        }
        $pdf = new Pdf();
        $pdf->AddPage('L');
        $pdf->SetMargins(5, 10);
        $pdf->SetFont('arial', 'B', 16);

        //        $pdf->Image('img/transparent.png', 1, 1, 1, 1);
        $pdf->Cell(0, 10, \utf8_decode('Cuentas por Cobrar'), 0, 1, 'C');
        $pdf->Ln(10);

        $col1 = $pdf->pageWidth() * 0.07;
        $col2 = $pdf->pageWidth() * 0.1;
        $col3 = $pdf->pageWidth() * 0.46;
        $pdf->SetFontSize(11);
        $pdf->Cell($col2, 8, 'Folio Interno', 'B', 0, 'C');
        $pdf->Cell($col2, 8, 'Fecha Factura', 'B', 0, 'C');
        $pdf->Cell($col3, 8, 'Receptor', 'B', 0, 'C');
        $pdf->Cell($col1, 8, 'Tipo', 'B', 0, 'C');
        $pdf->Cell($col1, 8, 'Moneda', 'B', 0, 'C');
        $pdf->Cell($col2, 8, 'Total', 'B', 0, 'C');
        $pdf->Cell($col2, 8, 'Pendiente', 'B', 1, 'C');

        $pdf->SetFont('arial', '', 10);
        $total_total_usd = 0;
        $total_pendiente_usd = 0;
        $total_total_mxn = 0;
        $total_pendiente_mxn = 0;
        foreach ($facturas as $factura) {
            if (!$pdf->espacioParaNotas(30)) {
                $pdf->AddPage('L');
                $pdf->SetMargins(5, 10);
                $pdf->SetFont('arial', '', 10);
            }
            if ($factura->moneda === 'USD') {
                $total_total_usd += $factura->total;
                $total_pendiente_usd += $factura->pendiente_ingresar;
            } elseif ($factura->moneda === 'MXN') {
                $total_total_mxn += $factura->total;
                $total_pendiente_mxn += $factura->pendiente_ingresar;
            }

            $posY = $pdf->GetY();
            $pdf->SetX($col2 * 2 + 5);
            $pdf->MultiCell($col3, 7, $factura->cliente_receptor, 'B', 'C');
            $height = $pdf->GetY() - $posY;
            $pdf->SetXY(5, $posY);

            $pdf->Cell($col2, $height, $factura->folio_interno, 'B', 0, 'C');
            $pdf->Cell($col2, $height, $factura->fecha_emision, 'B', 0, 'C');

            if ($factura->es_nota_credito)
                $tipo = 'NOT.CRE.';
            elseif ($factura->es_complemento)
                $tipo = 'COMP';
            else
                $tipo = 'FACT';
            $pdf->SetX($col2 * 2 + $col3 + 5);
            $pdf->Cell($col1, $height, $tipo, 'B', 0, 'C');
            $pdf->Cell($col1, $height, $factura->moneda, 'B', 0, 'C');
            $pdf->Cell($col2, $height, number_format($factura->total, 2), 'B', 0, 'C');
            $pdf->Cell($col2, $height, number_format($factura->pendiente_ingresar, 2), 'B', 1, 'C');
        }
        $pdf->SetFont('arial', 'B', 10);
        $pdf->Cell($col1 + $col2 * 2 + $col3, 14, 'Totales:', 'B', 0, 'R');
        $pdf->SetFont('arial', 'B', 10);
        $pdf->Cell($col1, 7, 'MXN:', 'B', 0, 'C');
        $pdf->Cell($col2, 7, number_format($total_total_mxn, 2), 'B', 0, 'C');
        $pdf->Cell($col2, 7, number_format($total_pendiente_mxn, 2), 'B', 1, 'C');
        $pdf->SetX($col1 + $col2 * 2 + $col3 + 5);
        $pdf->Cell($col1, 7, 'USD:', 'B', 0, 'C');
        $pdf->Cell($col2, 7, number_format($total_total_usd, 2), 'B', 0, 'C');
        $pdf->Cell($col2, 7, number_format($total_pendiente_usd, 2), 'B', 1, 'C');

        $pdf->Output('F', 'Reporte de Cuentas por Cobrar.pdf');

        return ['success' => true, 'report' => FacadesRequest::root() . "/Reporte de Cuentas por Cobrar.pdf?" . time()];
    }
}
