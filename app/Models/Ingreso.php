<?php

namespace App\Models;

use App\Http\Libraries\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class Ingreso
 * @package App\Models\Ingreso
 * @version June 10, 2019, 2:26 am UTC
 *
 * @property \datetime $fecha
 * @property string $comentarios
 * @property integer $info_contable_id
 */
class Ingreso extends Model
{
    public $table = 'tb_ingresos';

    protected $appends = ['fecha_str', 'folio_interno', 'monto'];

    public $fillable = [
        'fecha',
        'comentarios',
        'info_contable_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'fecha' => 'datetime',
        'comentarios' => 'string',
        'info_contable_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'fecha' => 'required'
    ];

    public function getFechaStrAttribute()
    {
        return $this->fecha ? $this->fecha->format('d/m/Y') : '';
    }

    public function getFolioInternoAttribute()
    {
        if ($this->facturas()->count() > 0)
            return Str::replaceLast(', ', '', $this->facturas()->pluck('folio_interno', 'id')->join(', '));
        return '';
    }

    public function getMontoAttribute()
    {
        return DB::table('tb_ingresos_facturas')
            ->where('ingreso_id', $this->id)
            ->sum('monto');
    }

    public function facturas()
    {
        return $this->belongsToMany(Factura::class, 'tb_ingresos_facturas', 'ingreso_id', 'factura_id')
            ->withPivot(['nota_credito_id', 'monto', 'monto_moneda_original', 'moneda']);
    }

    public static function reporteFilter(Request $request)
    {
        $query = Ingreso::query();
        $attr['fecha_inicio'] = null;
        $attr['fecha_fin'] = null;
        $attr['cliente'] = null;
        $attr['moneda'] = null;
        $attr['importe'] = null;

        if ($request->fecha_inicio && $request->fecha_fin) {
            $attr['fecha_inicio'] = $request->fecha_inicio;
            $attr['fecha_fin'] = $request->fecha_fin;
            $query->whereRaw("DATE(fecha) >= '" . Carbon::createFromFormat('d/m/Y', $attr['fecha_inicio'])->format('Y-m-d') . "' and
            DATE(fecha) <= '" . Carbon::createFromFormat('d/m/Y', $attr['fecha_fin'])->format('Y-m-d') . "'");
        } elseif ($request->fecha_inicio && !$request->fecha_fin) {
            $attr['fecha_inicio'] = $request->fecha_inicio;
            $query->whereRaw("DATE(fecha) >= '" . Carbon::createFromFormat('d/m/Y', $attr['fecha_inicio'])->format('Y-m-d') . "'");
        } elseif (!$request->fecha_inicio && $request->fecha_fin) {
            $attr['fecha_fin'] = $request->fecha_fin;
            $query->whereRaw("DATE(fecha) <= '" . Carbon::createFromFormat('d/m/Y', $attr['fecha_fin'])->format('Y-m-d') . "'");
        }

        //        dd($request->input());
        if ($request->cliente) {
            $attr['cliente'] = $request->cliente;
            $query->whereHas('factura', static function ($q) use ($request) {
                $q->where(static function ($query) use ($request) {
                    if ($request->cliente === '-1')
                        $query->orWhere('cliente_id', '<>', 0);
                    else
                        $query->orWhere('cliente_id', $request->cliente);
                });
            });
        }
        if ($request->moneda) {
            $attr['moneda'] = $request->moneda;
            if ($request->moneda !== '-1') {
                $query->whereHas('factura', static function ($q) use ($request) {
                    $q->where(static function ($query) use ($request) {
                        $query->orWhere('moneda', $request->moneda);
                    });
                });
            }
        }
        if ($request->importe) {
            $attr['importe'] = $request->importe;
            $query->where('monto', $request->importe);
        }

        $query->orderBy('fecha', 'desc');

        return [
            'query' => $query,
            'attr' => $attr
        ];
    }

    public static function imprimirIngresoPdf($ingreso_id, $mailing = false)
    {
        $pdf = new Pdf();
        $pdf->AddPage('P');
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetFont('Arial', 'B', 8);
        $ingreso = Ingreso::find($ingreso_id);
        $owner = get_system_owner();

        $col_width_1 = ($pdf->GetPageWidth() - 10) * 0.5;
        $col_width_2 = ($pdf->GetPageWidth() - 10) * 0.5;

        $y_inicial = $pdf->GetY();
        $pdf->Image(public_path() . '/img/BLANCO.png', 15, 10, 25, 17);

        $pdf->SetX(($pdf->GetPageWidth() - 10) - $col_width_1);
        //todo Escribiendo la cabcera DERECHA
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "Ingreso por Factura", 0, 1, 'R');
        $pdf->SetX(($pdf->GetPageWidth() - 10) - $col_width_1);
        $pdf->SetFont('Arial', 'B', 8);
        $width = $ingreso->factura->folio_interno ? ($col_width_1 - 10) : 0;
        $pdf->Cell($width, 5, 'Folio Factura: ', 0, 0, 'R');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 5, $ingreso->factura->folio_interno, 0, 1, 'R');
        $pdf->SetX(($pdf->GetPageWidth() - 10) - $col_width_1);
        $width = $owner->codigo_postal ? $col_width_1 - 3 : 0;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($width, 5, utf8_decode('Lugar de expedición: '), 0, 0, 'R');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 5, $owner->codigo_postal, 0, 1, 'R');

        $pdf->SetY(24);
        $pdf->SetX(10);
        $pdf->SetFont('Arial', 'B', 10);
        //todo Escribiendo la cabecera IZQUIERDA
        $pdf->SetX(15);
        $pdf->SetFont('arial', '', 17);
        $pdf->Cell($col_width_2, 7, '+52 998 8479278', 0, 1);
        $pdf->Cell($col_width_2, 7, 'www.wifiempresarial.com', 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('arial', '', 9);
        $pdf->Cell($col_width_2, 5, 'COMERCIO ELECTRONICO DOMINGUEZ & BALLESTER S. DE R.L DE C.V', 0, 1);
        $pdf->SetFont('arial', 'B', 9);
        $pdf->Cell(10, 5, 'RFC: ', 0, 0);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell($col_width_2, 5, $owner->rfc, 0, 1);
        $pdf->SetFont('arial', 'B', 9);
        $pdf->Cell(25, 5, utf8_decode('Régimen fiscal: '), 0, 0);
        $pdf->SetFont('arial', '', 9);
        $pdf->Cell($col_width_2, 5, $owner->regimen_fiscal->nombre, 0, 1);
        $pdf->WriteHTML('<b>Domicilio Fiscal: </b>' . utf8_decode($owner->direccion_fiscal->direccion_formateada), 5, $col_width_2, 1);
        $pdf->Ln(7);
        $pdf->Line(5, 8, $pdf->GetPageWidth() - 5, 8);
        $pdf->Line(5, 8, $pdf->GetX(), $pdf->GetY());
        $pdf->Line(5, $pdf->GetY(), $pdf->GetPageWidth() - 5, $pdf->GetY());
        $pdf->Line($pdf->GetPageWidth() - 5, $pdf->GetY(), $pdf->GetPageWidth() - 5, 8);
        $pdf->Ln(2);
        $pdf->WriteHTML('<b>Cliente: </b> ' . utf8_decode($ingreso->factura->cliente->razon_social) . ' <b>RFC: </b>' . $ingreso->factura->cliente->rfc, 5, '', 1);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>Domicilio Fiscal: </b>' . utf8_decode($ingreso->factura->cliente->direccion_fiscal->direccion_formateada), 5, '', 1);
        $pdf->Ln(5);
        $cots = '';
        $cots_arr = [];
        $ingreso->factura->factura_conceptos->map(function ($f_c) use (&$cots, &$cots_arr) {
            if ($f_c->cotizacion && $f_c->cotizacion->consecutivo && !in_array($f_c->cotizacion->consecutivo, $cots_arr, true)) {
                $cots .= $f_c->cotizacion->consecutivo . ', ';
                $cots_arr[] = $f_c->cotizacion->consecutivo;
            }
        });
        $cotizaciones = Str::replaceLast(', ', ' y ', Str::replaceLast(', ', '', $cots));
        $pdf->WriteHTML('<b>Cotizaciones: </b>' . $cotizaciones, 5, '', 1);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>Monto Cobrado: $</b>' . number_format($ingreso->monto, 2), 5, '', 1);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>Fecha de Cobro: </b>' . $ingreso->fecha->format('d/m/Y'), 5, '', 1);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>Comentarios: </b>' . utf8_decode($ingreso->comentarios), 5, '', 1);

        if ($mailing) {
            $pdf->Output('F', 'Ingreso.pdf');
            return "Ingreso.pdf";
        }

        $pdf->Output('I', 'Ingreso.pdf');
    }
}
