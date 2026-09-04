<?php

namespace App\Http\Controllers;

use App\Http\Libraries\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            ->leftJoin('tb_clientes as cliente', 'factura.cliente_id', '=', 'cliente.id');
        // Sin join a tb_factura_conceptos: ninguna columna f_c.* se usa y el
        // self-join anterior (f_c.factura_id = f_c.id) generaba cartesiano.

        // Rangos sargables con bindings (pueden usar indice). Equivalen al
        // filtro por dia completo del DATE() anterior.
        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->where('factura.fecha_certificacion', '>=', $request->fecha_inicio . ' 00:00:00')
                ->where('factura.fecha_certificacion', '<=', $request->fecha_fin . ' 23:59:59');
        } elseif ($request->fecha_inicio && !$request->fecha_fin) {
            $query->where('factura.fecha_certificacion', '>=', $request->fecha_inicio . ' 00:00:00');
        } elseif (!$request->fecha_inicio && $request->fecha_fin) {
            $query->where('factura.fecha_certificacion', '<=', $request->fecha_fin . ' 23:59:59');
        }

        if ($request->cliente && $request->cliente != -1) {
            $query->where('factura.cliente_id', $request->cliente);
        }

        if ($request->folio_interno) {
            $query->where('factura.folio_interno', 'like', "%$request->folio_interno%");
        }

        if ($request->importe) {
            // Equivale a ROUND(total) = N para totales >= 0, pero sargable y con binding.
            $n = round($request->importe);
            $query->where('factura.total', '>=', $n - 0.5)->where('factura.total', '<', $n + 0.5);
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
        // Subconsulta: el having() sobre el alias no sobrevive al COUNT del paginador,
        // por eso se pagina sobre la subconsulta ya filtrada.
        $sub = $this->query($request)->having('pendiente_ingresar', '>', 0);
        $facturas = DB::query()->fromSub($sub, 'f')
            ->paginate($request->perPage ?: 10, ['*'], 'page', $request->page ?: 1);

        // Decrypt solo de la pagina actual (antes: toda la tabla).
        $facturas->getCollection()->each(function ($factura) {
            $factura->cliente_receptor = Crypt::decrypt($factura->cliente_receptor);
            $factura->correo_cliente = Crypt::decrypt($factura->correo_cliente);
        });

        return ['success' => true, 'data' => $facturas];
    }

    public function imprimirListadoCuentasCobrar(Request $request)
    {
        // monto_ingresado/pendiente_ingresar ya vienen calculados por subselect en
        // query(): sin N+1 (antes Factura::find + ingresos->each por fila, que ademas
        // ignoraba notas de credito y discrepa del listado).
        $facturas = $this->query($request)->having('pendiente_ingresar', '>', 0)->get();
        foreach ($facturas as $factura) {
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

        // PDF fuera del web root (storage/app/pdfs) + URL de descarga autorizada.
        // La limpieza de archivos >24h vive en el scheduler (Kernel).
        Storage::makeDirectory('pdfs');
        $filename = 'cuentas-cobrar-' . date('YmdHis') . '-' . Str::random(6) . '.pdf';
        $pdf->Output('F', storage_path('app/pdfs/' . $filename));

        return ['success' => true, 'report' => route('admin.cuentas-cobrar.pdf', ['f' => $filename])];
    }

    public function descargarPdf(Request $request, $f)
    {
        $f = basename($f);
        if (!str_ends_with($f, '.pdf') || str_contains($f, '..')) {
            abort(404);
        }
        $path = storage_path('app/pdfs/' . $f);
        if (!is_file($path)) {
            abort(404);
        }

        return response()->download($path, 'Reporte de Cuentas por Cobrar.pdf');
    }
}
