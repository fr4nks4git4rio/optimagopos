<?php

namespace App\Http\Libraries;

use App\Http\Libraries\fpdf\PDF_MC_Table;

class Pdf extends PDF_MC_Table
{

  protected $B = 0;
  protected $I = 0;
  protected $U = 0;
  protected $HREF = '';

  public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
  {
    parent::__construct($orientation, $unit, $size);
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

  function WriteHTML($html, $h = 8, $width = '', $ln = 2)
  {
    //Interprete de HTML
    $html = str_replace('\n', ' ', $html);
    $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($a as $i => $e){
      if ($i % 2 == 0){
        //Text
        if ($this->HREF)
          $this->PutLink($this->HREF, $e);
        else
          $this->Write($h, $e, '', $width, $ln);
      }else{
        //Etiqueta
        if ($e[0] == '/')
          $this->CloseTag(strtoupper(substr($e, 1)));
        else{
          //Extraer atributos
          $a2 = explode(' ', $e);
          $tag = strtoupper(array_shift($a2));
          $attr = array();
          foreach ($a2 as $v)
            if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
              $attr[strtoupper($a3[1])] = $a3[2];
          $this->OpenTag($tag, $attr);
        }
      }
    }
  }

  function OpenTag($tag, $attr)
  {
    //Etiqueta de apertura
    if ($tag == 'B' || $tag == 'I' || $tag == 'U')
      $this->SetStyle($tag, true);
    if ($tag == 'A')
      $this->HREF = $attr['HREF'];
    if ($tag == 'BR')
      $this->Ln(8);
  }

  function CloseTag($tag)
  {
    //Etiqueta de cierre
    if ($tag == 'B' || $tag == 'I' || $tag == 'U')
      $this->SetStyle($tag, false);
    if ($tag == 'A')
      $this->HREF = '';
  }

  function SetStyle($tag, $enable)
  {
    //Modificar estilo y escojer la fuente correspodiente
    $this->$tag += ($enable ? 1 : -1);
    $style = '';
    foreach (array('B', 'I', 'U') as $s){
      if ($this->$s > 0)
        $style .= $s;
    }
    $this->SetFont('', $style);
  }

  function PutLink($URL, $txt)
  {
    //Escribir un hiper-enlace
    $this->SetTextColor(0, 0, 255);
    $this->SetStyle('U', true);
    $this->Write(8, $txt, $URL);
    $this->SetStyle('U', false);
    $this->SetTextColor(0);
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