<?php

namespace App\Services\Timbrado;

// use DOMdoument;

class CfdiComplementoConstructorV4
{
    private $owner;
    private $receptor = null;
    private $rfcEmisor = '';

    private $nombreEmisor = '';

    private $xml = null;

    private $cuentaPredial = null;

    private $atributos = [];

    private $concepto = [];

    private $conceptos = [];

    private $impuestos_p = [];

    private $totales_pagos = [];

    private $datos_pago = [];

    private $doc_rel = [
        'IdDocumento' => '',
        'Serie' => '',
        'Folio' => '',
        'MonedaDR' => '',
        'EquivalenciaDR' => '1',
        'NumParcialidad' => '',
        'ImpSaldoAnt' => '',
        'ImpPagado' => '',
        'ImpSaldoInsoluto' => '',
        'ObjetoImpDR' => '01',
        'ImpuestosDR' => []
    ];

    private $docs_rel = [];

    function __construct($owner)
    {

        $this->owner = $owner;
        $this->xml = new \DOMdocument("1.0", "UTF-8");

        $this->cuentaPredial = '4011800201';

        $this->atributos = [
            'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
            'xmlns:cfdi' => "http://www.sat.gob.mx/cfd/4",
            'LugarExpedicion' => "77600",
            'Exportacion' => '01',
            'TipoDeComprobante' => "P",
            'Total' => "0",
            'Moneda' => "XXX",
            'SubTotal' => "0",
            'Fecha' => date("Y-m-d") . "T" . date("H:i:s"), //"2018-02-09T15:54:23",
            'Folio' => 111222,
            'Serie' => 'A',
            'Version' => "4.0",
            'xmlns:pago20' => 'http://www.sat.gob.mx/Pagos20',
            'xsi:schemaLocation' => "http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/Pagos20 http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd",
        ];

        $this->concepto = [
            'Importe' => "0",
            'ClaveProdServ' => "84111506",
            'ValorUnitario' => "0",
            'ObjetoImp' => '01',
            'Descripcion' => "Pago",
            'Cantidad' => "1",
            'ClaveUnidad' => "ACT",
        ];
        $this->conceptos[] = $this->concepto;

        $this->receptor = [
            'Rfc' => 'XAXX010101000',
            'Nombre' => 'PUBLICO EN GENERAL',
            'UsoCFDI' => "CP01", //Gastos en general,
            'RegimenFiscalReceptor' => "616",
            'DomicilioFiscalReceptor' => '77600'/*'01000'*/
        ];

        $this->modoPruebas();
    }

    public function setAtributoReceptor($key, $value)
    {
        if (isset($this->receptor[$key])) {
            $this->receptor[$key] = $value;
        } else {
            throw new \Exception("No existe el indice $key en receptor");
        }

        return $this;
    }

    public function setAtributoComplemento($key, $value)
    {

        if (isset($this->atributos[$key])) {
            $this->atributos[$key] = $value;
        } else {
            throw new \Exception("No existe el indice $key en atributos");
        }

        return $this;
    }



    public function setDatosPago($array)
    {
        $this->datos_pago = $array;
        return $this;
    }

    public function resetAtributosDocRel(){
        $this->doc_rel = [
            'IdDocumento' => '',
            'Serie' => '',
            'Folio' => '',
            'MonedaDR' => '',
            'EquivalenciaDR' => '1',
            'NumParcialidad' => '',
            'ImpSaldoAnt' => '',
            'ImpPagado' => '',
            'ImpSaldoInsoluto' => '',
            'ObjetoImpDR' => '01',
            'ImpuestosDR' => []
        ];
    }

    public function setAtributoDocRel($key, $value)
    {
        if (isset($this->doc_rel[$key])) {
            $this->doc_rel[$key] = $value;
        } else {
            throw new \Exception("No existe el indice $key en concepto");
        }

        return $this;
    }

    public function addImpuestoDR($key, $data)
    {
        $this->doc_rel['ImpuestosDR'][$key] = $data;
        return $this;
    }

    public function addImpuestoP($key, $data)
    {
        $this->impuestos_p[$key] = $data;
        return $this;
    }

    public function addDocToDocs()
    {
        $this->docs_rel[] = $this->doc_rel;
        return $this;
    }

    public function setTotalesPagos($key, $value)
    {
        $this->totales_pagos[$key] = $value;
    }

    public function modoProductivo()
    {
        $this->rfcEmisor = $this->owner->rfc;
        $this->nombreEmisor = $this->owner->razon_social;
        return $this;
    }

    public function modoPruebas()
    {
        $this->rfcEmisor = 'IIA040805DZ4';
        $this->nombreEmisor = 'INDISTRIA ILUMINADORA DE ALMACENES';
        return $this;
    }

    public function fnumero($numero, $decimals = 2)
    {
        return number_format($numero, $decimals, '.', '');
    }

    private function limpiatexto($cadena)
    {
        $cadena = str_replace("Ñ", "N", strtoupper(trim($cadena)));
        return preg_replace("/[^A-Za-z0-9?![:space:]]/", " ", trim($cadena));
    }

    public function generarXML($datos = array())
    {

        $xml = &$this->xml;

        $atributos = $this->atributos;


        $emisor = array(
            'Rfc' => $this->rfcEmisor,
            'Nombre' => $this->nombreEmisor,
            'RegimenFiscal' => "601"
        );

        $receptor = $this->receptor;

        $conceptos = $this->conceptos;

        // Crea el nodo Xml Raiz
        $xml_root = $xml->createElement("cfdi:Comprobante");
        $xml->appendChild($xml_root);
        foreach (array_keys($atributos) as $elem) {
            $xml_root->setAttribute($elem, $atributos[$elem]);
        }

        // Crea el nodo del emisor
        $xml_emisor = $xml->createElement("cfdi:Emisor");
        $xml_root->appendChild($xml_emisor);
        foreach (array_keys($emisor) as $elem) {
            $xml_emisor->setAttribute($elem, $emisor[$elem]);
        }

        // Crea el nodo del receptor
        $xml_receptor = $xml->createElement("cfdi:Receptor");
        $xml_root->appendChild($xml_receptor);
        foreach (array_keys($receptor) as $elem) {

            $xml_receptor->setAttribute($elem, $receptor[$elem]);
        }

        ///////////////////////////////////////////////////////////
        //// C O N C E P T O S
        ///////////////////////////////////////////////////////////


        // Crea el nodo de conceptos
        $iva = 0;
        $xml_conceptos = $xml->createElement("cfdi:Conceptos");
        foreach ($conceptos as $concepto) {
            // Crea el nodo del concepto dentro de los conceptos
            $xml_concepto = $xml->createElement("cfdi:Concepto");
            foreach (array_keys($concepto) as $key) {
                $xml_concepto->setAttribute($key, $concepto[$key]);
            }

            $xml_conceptos->appendChild($xml_concepto);
        }

        $xml_root->appendChild($xml_conceptos);

        ///////////////////////////////////////////////////////////
        //// END OF CONCEPTOS
        ///////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////
        /// DATOS COMPLEMENTO
        //////////////////////////////////////////////////////////

        $xml_complementos = $xml->createElement("cfdi:Complemento");

        $xml_pagos20 = $xml->createElement("pago20:Pagos");
        $xml_pagos20->setAttribute('Version', "2.0");

        $xml_pagosTotales = $xml->createElement("pago20:Totales");
        foreach (array_keys($this->totales_pagos) as $key) {
            $xml_pagosTotales->setAttribute($key, $this->totales_pagos[$key]);
        }
        $xml_pagos20->appendChild($xml_pagosTotales);

        $xml_pago20 = $xml->createElement("pago20:Pago");
        foreach (array_keys($this->datos_pago) as $key) {
            $xml_pago20->setAttribute($key, $this->datos_pago[$key]);
        }
        foreach ($this->docs_rel as $doc) {
            $xml_docToRel = $xml->createElement("pago20:DoctoRelacionado");
            foreach (array_keys($doc) as $key) {
                if (!in_array($key, ['ImpuestosDR'])) {
                    $xml_docToRel->setAttribute($key, $doc[$key]);
                }
            }

            if (count($doc['ImpuestosDR']) > 0) {
                $xml_ImpuestosDR = $xml->createElement("pago20:ImpuestosDR");
                foreach (array_keys($doc['ImpuestosDR']) as $key) {
                    $xml_imp = $xml->createElement("pago20:$key");
                    $xml_nodo = $xml->createElement("pago20:{$doc['ImpuestosDR'][$key]['nodo']}");
                    foreach (array_keys($doc['ImpuestosDR'][$key]) as $k) {
                        if ($k != 'nodo') {
                            $xml_nodo->setAttribute($k, $doc['ImpuestosDR'][$key][$k]);
                        }
                    }
                    $xml_imp->appendChild($xml_nodo);
                    $xml_ImpuestosDR->appendChild($xml_imp);
                }
                $xml_docToRel->appendChild($xml_ImpuestosDR);
            }

            $xml_pago20->appendChild($xml_docToRel);
        }

        if (count($this->impuestos_p) > 0) {
            $xml_ImpuestosP = $xml->createElement("pago20:ImpuestosP");
            foreach (array_keys($this->impuestos_p) as $key) {
                $xml_imp = $xml->createElement("pago20:$key");
                $xml_nodo = $xml->createElement("pago20:{$this->impuestos_p[$key]['nodo']}");
                foreach (array_keys($this->impuestos_p[$key]) as $k) {
                    if ($k != 'nodo') {
                        $xml_nodo->setAttribute($k, $this->impuestos_p[$key][$k]);
                    }
                }
                $xml_imp->appendChild($xml_nodo);
                $xml_ImpuestosP->appendChild($xml_imp);
            }
            $xml_pago20->appendChild($xml_ImpuestosP);
        }

        $xml_pagos20->appendChild($xml_pago20);

        $xml_complementos->appendChild($xml_pagos20);

        $xml_root->appendChild($xml_complementos);

        ///////////////////////////////////////////////////////////
        /// END COMPLEMENTO
        //////////////////////////////////////////////////////////

        // General el XML
        return $xml->saveXML();
    }
}
