<?php

namespace App\Models;

use App\Http\Libraries\PdfFacturacion;
use App\Models\Cfdi;
use App\Models\ClaveUnidad;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\Serie;
use App\Models\TipoComprobante;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Factura
 * @package App\Models\Ventas
 * @version October 6, 2019, 12:01 pm CDT
 *
 * @property string $id
 * @property string $folio_interno
 * @property string $lugar_expedicion
 * @property string $fecha_emision
 * @property string $fecha_certificacion
 * @property string $comentarios
 * @property string $cantidad_letras
 * @property string $estado
 * @property string $moneda
 * @property string $direccion_xml
 * @property string $direccion_codigo_qr
 * @property string $uuid
 * @property string $cadena_original
 * @property string $numero_serie_sat
 * @property string $numero_serie_emisor
 * @property string $serie_certificado
 * @property string $sello_digital_sat
 * @property string $sello_digital_cfdi
 * @property boolean $modo_prueba_cfdi
 * @property string $cert_rfc_proveedor
 * @property float $porciento_iva
 * @property float $subtotal
 * @property float $iva
 * @property float $total
 * @property string $version_cfdi_timbrado
 * @property float $tipo_cambio
 * @property float $anio
 * @property string $cfdis_relacionados
 * @property integer $cfdi_id
 * @property integer $metodo_pago_id
 * @property integer $forma_pago_id
 * @property integer $cliente_id
 * @property integer $serie_id
 * @property integer $tipo_comprobante_id
 * @property integer $propietario_id
 * @property integer $tipo_relacion_factura_id
 * @property integer $motivo_cancelacion_id
 * @property integer $periodicidad_id
 * @property integer $mes_id
 * @property integer $user_id
 */
class Factura extends Model
{
    use HasFactory;

    public $table = 'tb_facturas';

    public $fillable = [
        'folio_interno',
        'lugar_expedicion',
        'fecha_emision',
        'fecha_certificacion',
        'comentarios',
        'cantidad_letras',
        'estado',
        'moneda',
        'direccion_xml',
        'direccion_codigo_qr',
        'uuid',
        'cadena_original',
        'numero_serie_sat',
        'numero_serie_emisor',
        'serie_certificado',
        'sello_digital_sat',
        'sello_digital_cfdi',
        'modo_prueba_cfdi',
        'cert_rfc_proveedor',
        'porciento_iva',
        'subtotal',
        'iva',
        'total',
        'version_cfdi_timbrado',
        'tipo_cambio',
        'anio',
        'cfdis_relacionados',
        'cfdi_id',
        'metodo_pago_id',
        'forma_pago_id',
        'cliente_id',
        'serie_id',
        'tipo_comprobante_id',
        'propietario_id',
        'tipo_relacion_factura_id',
        'motivo_cancelacion_id',
        'periodicidad_id',
        'mes_id',
        'user_id'
    ];

    protected $appends = [
        'fecha_certificacion_str',
        'fecha_certificacion_en',
        'fecha_emision_str',
        'fecha_emision_en',
        'direccion_xml_relativa',
        'direccion_codigo_qr_relativa',
        'label_combo',
        'label',
        'value'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'folio_interno' => 'string',
        'lugar_expedicion' => 'string',
        'fecha_emision' => 'datetime',
        'fecha_certificacion' => 'date',
        'comentarios' => 'string',
        'cantidad_letras' => 'string',
        'estado' => 'string',
        'moneda' => 'string',
        'direccion_xml' => 'string',
        'direccion_codigo_qr' => 'string',
        'uuid' => 'string',
        'cadena_original' => 'string',
        'numero_serie_sat' => 'string',
        'numero_serie_emisor' => 'string',
        'serie_certificado' => 'string',
        'sello_digital_sat' => 'string',
        'sello_digital_cfdi' => 'string',
        'modo_prueba_cfdi' => 'boolean',
        'cert_rfc_proveedor' => 'string',
        'porciento_iva' => 'float',
        'subtotal' => 'float',
        'iva' => 'float',
        'total' => 'float',
        'anio' => 'integer',
        'version_cfdi_timbrado' => 'string',
        'tipo_cambio' => 'float',
        'cfdis_relacionados' => 'string',
        'cfdi_id' => 'integer',
        'metodo_pago_id' => 'integer',
        'forma_pago_id' => 'integer',
        'cliente_id' => 'integer',
        'serie_id' => 'integer',
        'tipo_comprobante_id' => 'integer',
        'propietario_id' => 'integer',
        'tipo_relacion_factura_id' => 'integer',
        'motivo_cancelacion_id' => 'integer',
        'periodicidad_id' => 'integer',
        'mes_id' => 'integer',
        'user_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public function rules()
    {
        return [
            'estado' => ['nullable'],
            'comentarios' => ['nullable'],
            'cantidad_letras' => ['nullable'],
            'fecha_emision' => ['required'],
            'cliente_id' => ['required', 'exists:tb_empresas,id'],
            'cfdi_id' => ['required', 'exists:tb_cfdis,id'],
            'serie_id' => ['required', 'exists:tb_series,id'],
            'forma_pago_id' => ['required', 'exists:tb_forma_pagos,id'],
            'metodo_pago_id' => ['required', 'exists:tb_metodo_pagos,id'],
            'tipo_comprobante_id' => ['required', 'exists:tb_tipo_comprobantes,id'],
            'moneda' => ['required'],
            'tipo_cambio' => ['required'],
            'factura_conceptos' => ['array', 'min:1'],
            'factura_conceptos.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'factura_conceptos.*.precio_unitario' => ['required', 'numeric', 'gt:0'],
            'factura_conceptos.*.clave_prod_serv_id' => ['required', 'exists:tb_clave_prod_servs,id'],
            'factura_conceptos.*.objeto_impuesto_id' => ['required', 'exists:tb_objetos_impuesto,id'],
            'factura_conceptos.*.clave_unidad_id' => ['required', 'exists:tb_clave_unidades,id']
        ];
    }

    public function messages()
    {
        return [
            'fecha_emision.required' => 'Campo requerido.',
            'cliente_id.required' => 'Campo requerido.',
            'cliente_id.exists' => 'El Cliente no existe.',
            'cfdi_id.required' => 'Campo requerido.',
            'cfdi_id.exists' => 'El CFDI no existe.',
            'serie_id.required' => 'Campo requerido.',
            'serie_id.exists' => 'La Serie no existe.',
            'forma_pago_id.required' => 'Campo requerido.',
            'forma_pago_id.exists' => 'La Forma de Pago no existe.',
            'metodo_pago_id.required' => 'Campo requerido.',
            'metodo_pago_id.exists' => 'El Método de Pago no existe.',
            'tipo_comprobante_id.required' => 'Campo requerido.',
            'tipo_comprobante_id.exists' => 'El Tipo de Comprobante no existe.',
            'moneda.required' => 'Campo requerido.',
            'tipo_cambio.required' => 'Campo requerido.',
            'factura_conceptos.required' => 'El Concepto es requerido.',
            'factura_conceptos.min' => 'El Concepto es requerido.',
            'factura_conceptos.*.cantidad.required' => 'Campo requerido.',
            'factura_conceptos.*.cantidad.gt' => 'La cantidad debe ser mayor a 0.',
            'factura_conceptos.*.precio_unitario.required' => 'Campo requerido.',
            'factura_conceptos.*.precio_unitario.gt' => 'El precio unitario debe ser mayor a 0.',
            'factura_conceptos.*.clave_prod_serv_id.required' => 'Campo requerido.',
            'factura_conceptos.*.clave_prod_serv_id.exists' => 'La CLave Prod. Serv. no existe.',
            'factura_conceptos.*.objeto_impuesto_id.required' => 'Campo requerido.',
            'factura_conceptos.*.objeto_impuesto_id.exists' => 'El Objeto Impuesto no existe.',
            'factura_conceptos.*.clave_unidad_id.required' => 'Campo requerido.',
            'factura_conceptos.*.clave_unidad_id.exists' => 'La CLave Unidad no existe.'
        ];
    }

    public function getFechaCertificacionStrAttribute()
    {
        return $this->fecha_certificacion ? Carbon::parse($this->fecha_certificacion)->format('d/m/Y H:i') : '';
    }

    public function getFechaCertificacionEnAttribute()
    {
        return $this->fecha_certificacion ? Carbon::parse($this->fecha_certificacion)->format('Y-m-d') : '';
    }

    public function getFechaEmisionStrAttribute()
    {
        return $this->fecha_emision ? Carbon::parse($this->fecha_emision)->format('d/m/Y H:i') : '';
    }

    public function getFechaEmisionEnAttribute()
    {
        return $this->fecha_emision ? Carbon::parse($this->fecha_emision)->format('Y-m-d') : '';
    }

    public function getDireccionXmlRelativaAttribute()
    {
        if (str_starts_with($this->direccion_xml, 'storage/'))
            return $this->direccion_xml;
        return "storage/" . $this->direccion_xml;
    }

    public function getDireccionCodigoQrRelativaAttribute()
    {
        if (str_starts_with($this->direccion_codigo_qr, 'storage/'))
            return $this->direccion_codigo_qr;
        return "storage/" . $this->direccion_codigo_qr;
    }

    public function getLabelComboAttribute()
    {
        return "Folio: " . ($this->folio_interno ? $this->folio_interno : "S/F") . " | Receptor: " . ($this->cliente ? $this->cliente->razon_social : 'S/R') . " | Monto: " . $this->total;
    }

    public function getLabelAttribute()
    {
        return $this->label_combo;
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public static function parseData($data = [])
    {
        foreach ($data as $key => $value) {
            if (in_array($key, [
                'cfdi_id',
                'metodo_pago_id',
                'forma_pago_id',
                'cliente_id',
                'serie_id',
                'tipo_comprobante_id',
                'propietario_id',
                'tipo_relacion_factura_id',
                'motivo_cancelacion_id',
                'periodicidad_id',
                'mes_id',
                'user_id',
            ]) && $value) {
                switch ($key) {
                    case 'cfdi_id':
                        $data['cfdi'] = DB::table('tb_cfdis')
                            ->selectRaw('id, CONCAT(codigo, " | ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'metodo_pago_id':
                        $data['metodo_pago'] = DB::table('tb_metodo_pagos')
                            ->selectRaw('id, CONCAT(codigo, " | ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'forma_pago_id':
                        $data['forma_pago'] = DB::table('tb_forma_pagos')
                            ->selectRaw('id, CONCAT(codigo, " | ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'cliente_id':
                        $data['cliente'] = DB::table('tb_clientes')
                            ->selectRaw('id, razon_social')->where('id', $value)->first()->razon_social;
                        break;
                    case 'serie_id':
                        $data['serie'] = DB::table('tb_series')
                            ->selectRaw('id, descripcion')->where('id', $value)->first()->descripcion;
                        break;
                    case 'tipo_comprobante_id':
                        $data['tipo_comprobante'] = DB::table('tb_tipo_comprobantes')
                            ->selectRaw('id, CONCAT(codigo, " | ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'propietario_id':
                        $data['propietario'] = DB::table('tb_sucursales')
                            ->selectRaw('id, razon_social')->where('id', $value)->first()->razon_social;
                        break;
                    case 'tipo_relacion_factura_id':
                        $data['tipo_relacion_factura'] = DB::table('tb_tipo_relacion_facturas')
                            ->selectRaw('id, CONCAT(codigo, " | ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'motivo_cancelacion_id':
                        $data['motivo_cancelacion'] = DB::table('tb_motivos_cancelacion_factura')
                            ->selectRaw('id, CONCAT(codigo, " | ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'periodicidad_id':
                        $data['periodicidad'] = DB::table('tb_periodicidades_factura')
                            ->selectRaw('id, CONCAT(clave, " | ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'mes_id':
                        $data['mes'] = DB::table('tb_meses')
                            ->selectRaw('id, CONCAT(clave, " | ", descripcion) as nombre')->where('id', $value)->first()->nombre;
                        break;
                    case 'user_id':
                        $data['user'] = DB::table('tb_usuarios')
                            ->selectRaw('id, CONCAT(nombre, " ", apellidos) as nombre')->where('id', $value)->first()->nombre;
                        break;
                }
            }
        }

        return $data;
    }

    public function cfdi()
    {
        return $this->belongsTo(Cfdi::class);
    }

    public function metodo_pago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    public function forma_pago()
    {
        return $this->belongsTo(FormaPago::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }

    public function tipo_comprobante()
    {
        return $this->belongsTo(TipoComprobante::class);
    }

    public function propietario()
    {
        return $this->belongsTo(Sucursal::class, 'propietario_id');
    }
    public function periodicidad()
    {
        return $this->belongsTo(PeriodicidadFactura::class);
    }
    public function mes()
    {
        return $this->belongsTo(Mes::class);
    }

    public function tipo_relacion_factura()
    {
        return $this->belongsTo(TipoRelacionFactura::class, 'tipo_relacion_factura_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function factura_conceptos()
    {
        return $this->hasMany(FacturaConcepto::class);
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticket_operaciones()
    {
        return $this->hasMany(TicketOperacion::class, 'factura_id');
    }

    public static function generatePdf($invoice_id, $mailing = false)
    {
        $invoice = Factura::find($invoice_id);
        $owner = Sucursal::decryptInfo($invoice->propietario);
        $cliente = Cliente::decryptInfo($invoice->cliente);
        $pdf = new PdfFacturacion($owner, $invoice);
        $pdf->AddPage('P');
        $pdf->SetMargins(5, 50, 5);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetY(70);

        $pdf->SetY(62);
        $pdf->SetFont('Arial', 'B', 10);
        //todo Escribiendo la cabecera IZQUIERDA
        $pdf->SetX(15);
        $pdf->Ln(2);
        $pdf->WriteHTML('<b>Receptor: </b> ' . utf8_decode($cliente->razon_social) . ' <b>RFC: </b>' . $cliente->rfc, 5, '', 1);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>Domicilio Fiscal: </b>' . utf8_decode($cliente->direccion_fiscal->direccion_formateada), 5, '', 1);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>' . utf8_decode('Régimen Fiscal:') . ' </b>' . utf8_decode(optional($cliente->regimen_fiscal)->nombre), 5, '', 1);
        //        $pdf->Ln(5);
        //        $pdf->WriteHTML('<b>Uso del CFDI: </b>' . utf8_decode($invoice->cfdi_id ? $invoice->cfdi->nombre : ''), 5, '', 1);
        $pdf->Ln(10);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(240, 240, 240);
        $pdf->SetLineWidth(0.5);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 4, 'CONCEPTOS', 1, 1, 'C', 1);
        $width = $pdf->GetPageWidth() - 10;

        if ($invoice->es_nota_credito || $invoice->version_cfdi_timbrado === '3.3') {
            $col_1 = $width * 7 / 100;
            $col_2 = $width * 16 / 100;
            $col_4 = $width * 57 / 100;
            $col_5 = $width * 10 / 100;
            $col_6 = $width * 10 / 100;
            $pdf->Cell($col_1, 4, 'Cantidad', 1, 0, 'R', 1);
            $pdf->Cell($col_2, 4, 'Unidad', 1, 0, 'C', 1);
            $pdf->Cell($col_4, 4, utf8_decode('Clave Prod Serv / Descripción'), 1, 0, 'L', 1);
            $pdf->Cell($col_5, 4, 'Precio Unitario', 1, 0, 'R', 1);
            $pdf->Cell($col_6, 4, 'Importe', 1, 1, 'R', 1);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 7);
            foreach ($invoice->factura_conceptos as $concepto) {
                if (!$pdf->espacioParaNotas(65)) {
                    $pdf->AddPage();
                    $pdf->SetFont('Arial', '', 7);
                }

                $x_inicial = $pdf->GetX();
                $y_inicial = $pdf->GetY();
                $pdf->SetX($col_1 + $col_2 + 5);
                if ($concepto->clave_prod_serv) {
                    $descripcion = $concepto->clave_prod_serv->codigo . " - " . $concepto->clave_prod_serv->nombre . "
   " . $concepto->descripcion;
                } else
                    $descripcion = $concepto->descripcion;
                $pdf->MultiCell($col_4, 4, utf8_decode($descripcion), 1);
                $y_final = $pdf->GetY();
                $pdf->SetY($y_inicial);
                $pdf->Cell($col_1, $y_final - $y_inicial, $concepto->cantidad, 1, 0, 'C');
                $pdf->Cell($col_2, $y_final - $y_inicial, optional($concepto->clave_unidad)->codigo . ' - ' . optional($concepto->clave_unidad)->descripcion, 1, 0, 'C');
                $pdf->SetX($col_1 + $col_2 + +$col_4 + 5);
                $pdf->Cell($col_5, $y_final - $y_inicial, '$ ' . number_format($concepto->precio_unitario, 2), 1, 0, 'R');
                $pdf->Cell($col_6, $y_final - $y_inicial, '$ ' . number_format($concepto->importe, 2), 1, 1, 'R');
            }
        } else {
            $col_1 = $width * 7 / 100;
            $col_2 = $width * 16 / 100;
            $col_4 = $width * 37 / 100;
            $col_7 = $width * 20 / 100;
            $col_5 = $width * 10 / 100;
            $col_6 = $width * 10 / 100;

            $pdf->Cell($col_1, 4, 'Cantidad', 1, 0, 'R', 1);
            $pdf->Cell($col_2, 4, 'Unidad', 1, 0, 'C', 1);
            $pdf->Cell($col_4, 4, utf8_decode('Clave Prod Serv / Descripción'), 1, 0, 'L', 1);
            $pdf->Cell($col_7, 4, 'Objeto Impuesto', 1, 0, 'C', 1);
            $pdf->Cell($col_5, 4, 'Precio Unitario', 1, 0, 'R', 1);
            $pdf->Cell($col_6, 4, 'Importe', 1, 1, 'R', 1);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 7);
            foreach ($invoice->factura_conceptos as $concepto) {
                if (!$pdf->espacioParaNotas(65)) {
                    $pdf->AddPage();
                    $pdf->SetFont('Arial', '', 7);
                }
                $x_inicial = $pdf->GetX();
                $y_inicial = $pdf->GetY();
                $pdf->SetX($col_1 + $col_2 + 5);
                if ($concepto->clave_prod_serv) {
                    $descripcion = $concepto->clave_prod_serv->codigo . " - " . $concepto->clave_prod_serv->nombre . "
   " . $concepto->descripcion;
                } else
                    $descripcion = $concepto->descripcion;
                $pdf->MultiCell($col_4, 4, utf8_decode($descripcion), 1);
                $y_final = $pdf->GetY();
                $pdf->SetY($y_inicial);
                $pdf->Cell($col_1, $y_final - $y_inicial, $concepto->cantidad, 1, 0, 'C');
                $pdf->Cell($col_2, $y_final - $y_inicial, optional($concepto->clave_unidad)->codigo . ' - ' . optional($concepto->clave_unidad)->descripcion, 1, 0, 'C');
                $pdf->SetX($col_1 + $col_2 + +$col_4 + 5);
                $pdf->Cell($col_7, $y_final - $y_inicial, optional($concepto->objeto_impuesto)->nombre, 1, 0, 'C');
                $pdf->Cell($col_5, $y_final - $y_inicial, '$ ' . number_format($concepto->precio_unitario, 2), 1, 0, 'R');
                $pdf->Cell($col_6, $y_final - $y_inicial, '$ ' . number_format($concepto->importe, 2), 1, 1, 'R');
            }
        }


        $pdf->Ln(10);

        $col_1 = $width * 15 / 100;
        $col_2 = $width * 52 / 100;
        $col_3 = $width * 20 / 100;
        $col_4 = $width * 13 / 100;
        $pos_inicial = $pdf->GetY();
        $pdf->SetX($width - ($col_3 + $col_4 - 5));
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($col_3, 4, 'SUBTOTAL', 0, 0, 'R');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($col_4, 4, '$ ' . number_format($invoice->subtotal, 2), 0, 1, 'R');
        $pdf->SetX($width - ($col_3 + $col_4 - 5));
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($col_3, 4, 'IVA TASA 16%', 0, 0, 'R');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($col_4, 4, '$ ' . number_format($invoice->iva, 2), 0, 1, 'R');
        if ($invoice->porciento_descuento > 0) {
            $pdf->SetX($width - ($col_3 + $col_4 - 5));
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell($col_3, 4, utf8_decode('Descuento TASA ') . $invoice->porciento_descuento . '%', 0, 0, 'R');
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell($col_4, 4, '$ ' . number_format($invoice->descuento, 2), 0, 1, 'R');
        }

        $pdf->SetX($width - ($col_3 + $col_4 - 5));
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($col_3, 4, 'TOTAL', 0, 0, 'R');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell($col_4, 4, '$ ' . number_format($invoice->total, 2), 0, 1, 'R');

        $pdf->SetY($pos_inicial);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($col_1, 4, 'IMPORTE CON LETRA', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetFillColor(232, 232, 232);
        $pdf->SetDrawColor(0, 0, 0);
        $cantidad_letras = convertir_numero_a_letras(round($invoice->total, 2), strtoupper($invoice->moneda));
        $pdf->MultiCell($col_1 + $col_2, 15, '         ' . $cantidad_letras, 1, '', 1);
        $pdf->SetFont('Arial', '', 7);
        $moneda = $invoice->moneda;
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->WriteHTML('<b>Moneda: </b>' . strtoupper($moneda) . ' - ' . (strtoupper($moneda) === 'MXN' ? 'Peso Mexicano ' : 'Dolar Estadounidense '), 5);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>' . utf8_decode('Método de Pago: ') . '</b>' . utf8_decode(optional($invoice->metodo_pago)->nombre), 5);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>Forma de pago: </b>' . utf8_decode(optional($invoice->forma_pago)->nombre), 5);
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>Uso de CFDI: </b>' . utf8_decode(optional($invoice->cfdi)->nombre ?? ''), 5);
        $pdf->Ln(5);
        if ($invoice->cliente_id === 57) {
            $pdf->WriteHTML('<b>Periodicidad: </b>' . utf8_decode((optional($invoice->periodicidad)->clave . ' | ' . optional($invoice->periodicidad)->descripcion) ?? ''), 5);
            $pdf->Ln(5);
            $pdf->WriteHTML('<b>Mes: </b>' . utf8_decode((optional($invoice->mes)->clave . ' | ' . optional($invoice->mes)->descripcion) ?? ''), 5);
            $pdf->Ln(5);
            $pdf->WriteHTML('<b>' . utf8_decode('Año:') . ' </b>' . $invoice->anio, 5);
            $pdf->Ln(5);
        }
        $pdf->SetFont('Arial', 'B', 7);
        if ($invoice->con_facturas_relacionadas) {
            $pdf->Cell(0, 4, 'CFDI Relacionados:', 0, 1);
            foreach ($invoice->facturas_relacionadas as $fact) {
                $pdf->SetX(10);
                $pdf->WriteHTML('<b>-  CFDI: </b>' . $fact->uuid . utf8_decode('   <b>Tipo Relación: </b>') . utf8_decode($invoice->tipo_relacion_factura->descripcion), 5, $col_1 + $col_2, 1);
                $pdf->Ln(5);
            }
        }
        $pdf->Ln(5);
        $pdf->WriteHTML('<b>Observaciones: </b>' . utf8_decode($invoice->comentarios), 5, $col_1 + $col_2, 1);

        $pdf->Ln(5);
        $pdf->Cell(0, 6, utf8_decode('Este documento es una representación impresa de un CFDI v' . $invoice->version_cfdi_timbrado), 0, 1);

        if (!$pdf->espacioParaNotas(100)) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', '', 7);
        }
        $pdf->Ln(5);

        $col_1 = $width * 20 / 100;
        $col_2 = $width * 80 / 100;
        $pos_inicial = $pdf->GetY();
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetX($col_1);
        $pdf->Cell($col_2, 4, 'SELLO DIGITAL DEL CFDI', 0, 1);
        $pdf->SetX($col_1);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell($col_2, 4, $invoice->sello_digital_cfdi);
        $pdf->SetX($col_1);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($col_2, 4, 'SELLO DIGITAL DEL SAT', 0, 1);
        $pdf->SetX($col_1);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell($col_2, 4, $invoice->sello_digital_sat);
        $pdf->SetX($col_1);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell($col_2, 4, utf8_decode('CADENA ORIGINAL DEL COMPLEMENTO DE CERTIFICACIÓN DIGITAL DEL SAT'), 0, 1);
        $pdf->SetX($col_1);
        $pdf->SetFont('Arial', '', 5);
        $pdf->MultiCell($col_2, 4, $invoice->cadena_original);
        $pos_final = $pdf->GetY();
        $pdf->SetY($pos_inicial);
        if ($invoice->direccion_codigo_qr && file_exists(public_path() . $invoice->direccion_codigo_qr_relativa)) {
            $pdf->Image(public_path() . $invoice->direccion_codigo_qr_relativa, 5, $pos_inicial, $col_1 - 5);
        }

        $name = utf8_decode('Factura_') . $invoice->folio_interno . '.pdf';
        if ($mailing) {
            $pdf->Output('F', $name);
            return $name;
        }
        $pdf->Output('I', $name);
    }

    public static function generateFacturaPdf($id, $mailing = false)
    {
        $factura = Factura::find($id);
        $name = 'Factura_' . $factura->folio_interno . '.pdf';
        $view = 'reports.factura.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            'name' => $name,
            'owner' => Cliente::decryptInfo($factura->propietario),
            'cliente' => Cliente::decryptInfo($factura->cliente),
            'factura' => $factura
        ]);
        $pdf->save($name);
        return $name;
    }

    /**
     * @param boolean $is_complement - default -> false
     * @param bool $is_nota_credito
     * @param bool $is_nomina
     * @param bool $is_nota_venta
     * @param boolean $modo_productivo
     * @return int
     */
    public static function internalSheetGenerator($serie_id, bool $modo_productivo = false)
    {
        $query = Factura::query();
        $query->whereIn('estado', ['TIMBRADA', 'CANCELADA'])
            ->where('serie_id', $serie_id)
            ->where('folio_interno', '!=', null);

        if ($modo_productivo) {
            $facturas = $query->where(function ($q) {
                $q->where('modo_prueba_cfdi', null)
                    ->orWhere('modo_prueba_cfdi', 0);
            })
                ->orderBy('fecha_certificacion')
                ->get();
            if ($facturas->count() > 0) {
                $last = $facturas->last();
                $consecutivo = explode('-', $last->folio_interno)[1];
                $folio = (int)$consecutivo + 1;
            } else {
                $folio = 1;
            }
        } else {
            $facturas = $query->where('modo_prueba_cfdi', 1)
                ->orderBy('fecha_certificacion')
                ->get();
            if ($facturas->count() > 0) {
                $last = $facturas->last();
                $arr = explode('-', $last->folio_interno);
                $consecutivo = count($arr) > 1 ? $arr[1] : 0;
                $folio = (int)$consecutivo + 1;
            } else {
                $folio = 1;
            }
            $folio .= '-TEST';
        }
        return $folio;
    }
}
