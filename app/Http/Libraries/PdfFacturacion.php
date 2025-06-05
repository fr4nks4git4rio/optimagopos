<?php

namespace App\Http\Libraries;

use App\Http\Libraries\fpdf\PDF_MC_Table;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Sucursal;
use Carbon\Carbon;

class PdfFacturacion extends Pdf
{

    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';
    protected $invoice = null;
    protected $owner = null;

    public function __construct(Sucursal $owner, Factura $invoice, $orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $this->invoice = $invoice;
        $this->owner = $owner;
        parent::__construct($orientation, $unit, $size);
    }

    public function Header()
    {
        $this->SetXY(5, 2);

        // Select Arial italic 8
        if ($this->invoice->estado == 'CANCELADA'){
            $this->Image(public_path() . '/img/cancelado.png', 50, 12);
        }
        $this->SetFont('arial', 'B', 14);
        $this->Cell(45, 7, utf8_decode(strtoupper($this->owner->razon_social)), 0, 0);
        $this->SetFont('arial', '', 13);
        $tipo_invoice = 'Factura';
        $this->Cell(0, 7, utf8_decode("$tipo_invoice ") . $this->invoice->folio_interno, 0, 1, 'R');
        $this->SetFont('arial', 'B', 10);
        $this->Cell(45, 5, utf8_decode($this->owner->razon_social), 0, 1);

        $this->Cell(9, 5, 'RFC:', 0, 0);
        $this->SetFont('arial', '', 10);
        $this->Cell(45, 5, $this->owner->rfc, 0, 1);
        $this->SetFont('arial', 'B', 10);
        $this->Cell(29, 5, utf8_decode('Dirección Fiscal:'), 0, 0);
        $this->SetFont('arial', '', 10);
        $this->MultiCell(45, 4, utf8_decode($this->owner->direccion_plain), 0, 1);
        $this->SetFont('arial', 'B', 10);
        $this->Cell(29, 5, utf8_decode('Régimen Fiscal:'), 0, 0);
        $this->SetFont('arial', '', 10);
        $this->Cell(45, 5, utf8_decode(optional($this->owner->regimen_fiscal)->nombre), 0, 1);
        $this->SetFont('arial', 'B', 10);
        $this->Cell(17, 5, utf8_decode('Teléfono:'), 0, 0);
        $this->SetFont('arial', '', 10);
        $this->Cell(45, 5, $this->owner->telefono_principal, 0, 1);

        $col_width_1 = ($this->GetPageWidth() - 10) * 0.5;
        $col_width_2 = ($this->GetPageWidth() - 10) * 0.5;
        $this->SetY(23);
        $this->SetX(($this->GetPageWidth() - 10) - $col_width_1);
        //todo Escribiendo la cabcera DERECHA
        $this->SetX(($this->GetPageWidth() - 10) - $col_width_1);
        $width = $this->invoice->uuid ? $col_width_1 - 47 : 0;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($width, 5, 'Folio fiscal: ', 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 5, $this->invoice->uuid, 0, 1, 'R');
        $this->SetX(($this->GetPageWidth() - 13) - $col_width_1);
        $width = $this->invoice->numero_serie_sat ? $col_width_1 - 25 : 0;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($width, 5, 'No. de serie del certificado del SAT: ', 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 5, $this->invoice->numero_serie_sat, 0, 1, 'R');
        $this->SetX(($this->GetPageWidth() - 13) - $col_width_1);
        $width = $this->invoice->numero_serie_emisor ? $col_width_1 - 25 : 0;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($width, 5, 'No. de serie del certificado del CSD: ', 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 5, $this->invoice->numero_serie_emisor, 0, 1, 'R');
        $this->SetX(($this->GetPageWidth() - 15) - $col_width_1);
        $width = $this->invoice->fecha_emision ? $col_width_1 - 19 : 0;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($width, 5, utf8_decode('Fecha y hora de emisión: '), 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $dia = Carbon::createFromFormat('Y-m-d H:i:s', $this->invoice->fecha_emision)->format('Y-m-d');
        $hora = explode(' ', Carbon::parse($this->invoice->fecha_emision)->format('Y-m-d H:i:s'))[1];
        $this->Cell(0, 5, $dia . 'T' . $hora, 0, 1, 'R');
        $this->SetX(($this->GetPageWidth() - 15) - $col_width_1);
        $width = $this->invoice->fecha_certificacion ? $col_width_1 - 19 : 0;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($width, 5, utf8_decode('Fecha y hora de certificación: '), 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 5, $this->invoice->fecha_certificacion ? (Carbon::parse($this->invoice->fecha_certificacion)->format('Y-m-d') . 'T' . Carbon::Parse($this->invoice->fecha_certificacion)->format('H:i:s')) : '', 0, 1, 'R');
        $this->SetX(($this->GetPageWidth() - 14) - $col_width_1);
        $width = $this->owner->codigo_postal ? $col_width_1 - 3 : 0;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($width, 5, utf8_decode('Lugar de expedición: '), 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 5, $this->owner->codigo_postal, 0, 1, 'R');
        $this->SetX(($this->GetPageWidth() - 14) - $col_width_1);
        $width = $this->invoice->tipo_comprobante ? $col_width_1 - 7 : 0;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($width, 5, 'Efecto del comprobante: ', 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 5, $this->invoice->tipo_comprobante->nombre, 0, 1, 'R');
        $this->SetX(($this->GetPageWidth() - 13) - $col_width_1);
        $width = $this->invoice->cert_rfc_proveedor ? $col_width_1 - 15 : 0;
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($width, 5, utf8_decode('RFC del proveedor de certificación: '), 0, 0, 'R');
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 5, $this->invoice->cert_rfc_proveedor, 0, 1, 'R');

        $this->Line(5, $this->GetY(), $this->GetPageWidth() - 5, $this->GetY());

    }

    public function pageWidth()
    {
        return $this->GetPageWidth() - ($this->lMargin + $this->rMargin);
    }

    public function leftMargin()
    {
        return $this->lMargin;
    }

    public function rightMargin()
    {
        return $this->rMargin;
    }

    function espacioParaNotas($tamanoNotas = 80)
    {
        if ($this->GetY() < ($this->GetPageHeight() - $tamanoNotas))
            return true;
        return false;
    }

    function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-10);

        // Select Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Print centered page number
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'R');
    }

    function MultiCellH($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {

    }

}
