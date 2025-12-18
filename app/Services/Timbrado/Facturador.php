<?php

namespace App\Services\Timbrado;

use App\Models\MotivoCancelacionFactura;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Sucursal;
use App\Services\Timbrado\CfdiConstructor;
use App\Services\Timbrado\CfdiTimbrador;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Facturador
{
    private $emisor;
    public bool $modo_productivo;

    public function __construct(Sucursal $propietario)
    {
        $this->emisor = $propietario;
        $this->modo_productivo = modo_facturacion() == 1;
    }


    /**
     * @throws \Exception
     */
    public function timbrarFactura($id, $folio)
    {
        date_default_timezone_set('America/Mexico_City');

        ///////////////////////////////////////////////////////////////////////
        //G E N E R A C I O N   D E   L A  F A C T U R A

        $factura = Factura::find($id);


        if (!$factura) {
            throw new \Exception('Factura no encontrada: ' . $id);
            return false;
        }

        //construimos el XML
        $Builder = new CfdiConstructor($factura->propietario);

        if ($this->modo_productivo === true) {
            $Builder->modoProductivo();
        } else {
            $Builder->modoPruebas();
        }

        ///////////////////////////////////////////////////////////////////////
        // HEAD SECTION

        $owner = $factura->propietario;
        $factura->folio_interno = $folio;
        $Builder->setAtributoFactura('Folio', $factura->folio_interno);
        $Builder->setAtributoFactura('LugarExpedicion', $factura->lugar_expedicion);

        $Builder->setAtributoFactura('Serie', $factura->serie->descripcion);

        $Builder->setAtributoFactura('SubTotal', $Builder->fnumero($factura->subtotal));

        //        $iva = $Builder->fnumero($factura->iva, 3);
        //        $Builder->setAtributoFactura('IVA', $Builder->fnumero($factura->iva, 4));

        $Builder->setAtributoFactura('TasaIVA', $Builder->fnumero($factura->porciento_iva / 100, 6));

        $Builder->setAtributoFactura('Moneda', $factura->moneda);
        if (strtoupper($factura->moneda) !== "MXN") {
            $Builder->setAtributoFactura('TipoCambio', $factura->tipo_cambio);
        }

        $Builder->setAtributoFactura('FormaPago', optional($factura->forma_pago)->codigo);
        $Builder->setAtributoFactura('MetodoPago', optional($factura->metodo_pago)->codigo);
        if ($factura->tipo_comprobante)
            $Builder->setAtributoFactura('TipoDeComprobante', optional($factura->tipo_comprobante)->codigo);

        if ($factura->cfdis_relacionados) {
            $Builder->setAtributoFactura('xsi:schemaLocation', "http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/detallista http://www.sat.gob.mx/sitio_internet/cfd/detallista/detallista.xsd http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd http://www.buzonfiscal.com/ns/addenda/bf/2 http://www.buzonfiscal.com/schema/xsd/Addenda_BF_v2.2.xsd");
        }

        ///////////////////////////////////////////////////////////////////////
        // RECEPTOR SECTION
        if ($factura->cliente->rfc == 'XAXX010101000') {
            $Builder->setAtributoInformacionGlobal('Periodicidad', $factura->periodicidad ? $factura->periodicidad->clave : '');
            $Builder->setAtributoInformacionGlobal('Meses', $factura->mes ? $factura->mes->clave : '');
            $Builder->setAtributoInformacionGlobal('Año', $factura->anio);
        }
        $usoCfdi = optional($factura->cfdi)->codigo;
        $Builder->setAtributoReceptor('UsoCFDI', $usoCfdi);
        $Builder->setAtributoReceptor('Rfc', $factura->cliente->rfc);
        $Builder->setAtributoReceptor('Nombre', Crypt::decrypt($factura->cliente->razon_social));
        $Builder->setAtributoReceptor('RegimenFiscalReceptor', $factura->cliente->regimen_fiscal->codigo ?? '');
        $Builder->setAtributoReceptor('DomicilioFiscalReceptor', $factura->cliente->direccion_fiscal->codigo_postal);



        ///////////////////////////////////////////////////////////////////////
        // ITEM SECTION
        if ($factura->cfdis_relacionados) {
            $Builder->setTipoRelacionFacturas($factura->tipo_relacion_factura->codigo);
            foreach (explode(',', $factura->cfdis_relacionados) as $fact) {
                $Builder->setAtributoCfdiRelacionado('UUID', $fact);

                $Builder->addCfdiRelacionadoToCfdiRelacionados();
            }
        }

        $iva = 0;
        foreach ($factura->factura_conceptos as $concepto) {
            //      $Builder->setAtributoConcepto('NoIdentificacion', $concepto->producto_servicio->id < 10 ? '0' . $concepto->producto_servicio->id : $concepto->producto_servicio->id); //<-PREGUNTAR
            $Builder->setAtributoConcepto('NoIdentificacion', '0'); //<-PREGUNTAR
            $Builder->setAtributoConcepto('ClaveProdServ', optional($concepto->clave_prod_serv)->codigo);
            $Builder->setAtributoConcepto('ClaveUnidad', optional($concepto->clave_unidad)->codigo);
            $Builder->setAtributoConcepto('ObjetoImp', $concepto->objeto_impuesto ? strtoupper($concepto->objeto_impuesto->clave) : '');
            $Builder->setAtributoConcepto('Unidad', optional($concepto->clave_unidad)->descripcion);
            $Builder->setAtributoConcepto('Descripcion', optional($concepto->clave_prod_serv)->nombre);

            $Builder->setAtributoConcepto('Cantidad', $concepto->cantidad);

            $Builder->setAtributoConcepto('ValorUnitario', $concepto->precio_unitario);
            $Builder->setAtributoConcepto('Base', $concepto->importe);
            $Builder->setAtributoConcepto('Importe', $concepto->importe);
            $iva += round($concepto->iva, 2);
            $Builder->setAtributoConcepto('IVA', $concepto->iva);

            $Builder->addConceptoToConceptos();
        }
        // $total = $factura->subtotal + $iva;
        $Builder->setAtributoFactura('Total', $Builder->fnumero($factura->total, 2));

        ///////////////////////////////////////////////////////////////////////
        // GENERAR XML

        $xmlOrig = $Builder->generarXML();

        if ($xmlOrig === false) {
            throw new \RuntimeException("Error al formar archivo XML ");
            //            return false;
        }

        //Guardamos el XML previo en disco
        //        $relative_path = config('app.cfdi_path') . "FD_$id/";
        //        $path = public_path() . $relative_path;
        //
        //        // dir doesn't exist, make it
        //        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
        //            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        //        }
        $name = 'FD_' . $id . '_PRE_' . date('YmdHis') . '.xml';
        //        $filename = $path . $name;

        //        file_put_contents($filename, $xmlOrig);

        //        dd("cfdi/FD_$id/$name");
        Storage::disk('public')->put("cfdi/FD_$id/$name", $xmlOrig);

        ///////////////////////////////////////////////////////////////////////
        // CERTIFICACION CFDI

        $Timbrador = new CfdiTimbrador($this->emisor);

        if ($this->modo_productivo) {
            $Timbrador->modo_productivo();
        } else {
            $Timbrador->modo_pruebas();
        }

        $factura->fecha_emision = now();
        $result = $Timbrador->timbra($id, $xmlOrig);


        if ($result['res'] == 1) {

            //guardamos el codigo qr
            $name_qr = 'FD_' . $id . '_' . $result['uuid'] . 'codigoQr.jpg';
            //            $file_qr = $path . $name_qr;
            //            file_put_contents($file_qr, $result['codigoQr']);
            Storage::disk('public')->put("cfdi/FD_$id/$name_qr", $result['codigoQr']);
            $factura->direccion_codigo_qr = "cfdi/FD_$id/$name_qr";

            //guardamos el xml timbrado
            $name_xml = 'FD_' . $id . '_' . $result['uuid'] . '.xml';
            //            $file_xml = $path . $name_xml;
            //            file_put_contents($file_xml, $result['xmlTimbrado']);
            Storage::disk('public')->put("cfdi/FD_$id/$name_xml", $result['xmlTimbrado']);
            $factura->direccion_xml = "cfdi/FD_$id/$name_xml";

            //seteamos los campos faltantes en factura
            $factura->uuid = $result['uuid'];
            $factura->cadena_original = $result['cadenaOriginal'];
            $factura->estado = 'TIMBRADA';

            //si esta en modo pruebas avisar en bd
            if ($this->modo_productivo == 0) {
                $factura->modo_prueba_cfdi = 1;
            }

            //            $dir = $file_xml;
            $p = xml_parser_create("UTF-8");
            //            $var = file_get_contents($dir);
            $var = Storage::disk('public')->get("cfdi/FD_$id/$name_xml");
            xml_parse_into_struct($p, $var, $vals, $index);
            xml_parser_free($p);

            $factura->fecha_certificacion = now();
            $factura->numero_serie_sat = $vals[$index['TFD:TIMBREFISCALDIGITAL'][0]]['attributes']['NOCERTIFICADOSAT'];
            $factura->cert_rfc_proveedor = $vals[$index['TFD:TIMBREFISCALDIGITAL'][0]]['attributes']['RFCPROVCERTIF'];
            $factura->sello_digital_cfdi = $vals[$index['TFD:TIMBREFISCALDIGITAL'][0]]['attributes']['SELLOCFD'];
            $factura->sello_digital_sat = $vals[$index['TFD:TIMBREFISCALDIGITAL'][0]]['attributes']['SELLOSAT'];
            $factura->serie_certificado = $vals[$index['CFDI:COMPROBANTE'][0]]['attributes']['CERTIFICADO'];
            $factura->numero_serie_emisor = $vals[$index['CFDI:COMPROBANTE'][0]]['attributes']['NOCERTIFICADO'];
            $factura->version_cfdi_timbrado = '4.0';

            $label = 'Factura';

            activity($label . ' Timbrada')
                ->on($factura)
                ->withProperties($factura->getDirty())
                ->log("Timbrada " . $label . " con folio interno: $factura->folio_interno");

            $factura->saveQuietly();

            $response = ['success' => true, 'message' => "Timbrado exitoso."];
        } else {
            $error = 'Error al timbrar: ' . $result['msg'];
            $response = ['success' => false, 'message' => $error];
        }
        return $response;
    }

    /**
     * @throws \Exception
     */
    public function timbrarComplemento($id, $folio)
    {
        date_default_timezone_set('America/Mexico_City');
        ///////////////////////////////////////////////////////////////////////
        //G E N E R A C I O N   D E   L A  F A C T U R A

        //    $id = $request->id;
        $complemento = Factura::find($id);

        if (!$complemento) {
            throw new \Exception('Complemento no encontrado: ' . $id);
        }

        $propietrario = Sucursal::decryptInfo($complemento->propietario);
        $cliente = Cliente::decryptInfo($complemento->cliente);
        //construimos el XML
        $Builder = new CfdiComplementoConstructorV4($propietrario);

        if ($this->modo_productivo === true) {
            $Builder->modoProductivo();
        } else {
            $Builder->modoPruebas();
        }

        ///////////////////////////////////////////////////////////////////////
        // HEAD SECTION
        $complemento->folio_interno = $folio;
        $Builder->setAtributoComplemento('Folio', $complemento->folio_interno);
        $Builder->setAtributoComplemento('LugarExpedicion', $complemento->lugar_expedicion);
        $Builder->setAtributoComplemento('Serie', $complemento->serie->descripcion);
        $Builder->setAtributoComplemento('TipoDeComprobante', $complemento->tipo_comprobante->codigo);

        ///////////////////////////////////////////////////////////////////////
        // RECEPTOR SECTION

        $Builder->setAtributoReceptor('Rfc', $cliente->rfc);
        $Builder->setAtributoReceptor('Nombre', $cliente->razon_social);
        $Builder->setAtributoReceptor('RegimenFiscalReceptor', $cliente->regimen_fiscal->codigo);
        $Builder->setAtributoReceptor('DomicilioFiscalReceptor', $cliente->direccion_fiscal->codigo_postal);

        $monto_total = 0;
        $complemento->facturas->each(function (Factura $factura) use (&$monto_total, $complemento) {
            if ($factura->moneda === 'USD')
                $monto_total += round($factura->pivot->importe_pagado * $complemento->tipo_cambio, 2);
            else
                $monto_total += round($factura->pivot->importe_pagado, 2);
        });

        $Builder->setTotalesPagos('MontoTotalPagos', $monto_total);

        ///////////////////////////////////////////////////////////////////////
        // ITEM SECTION

        //DATOS PAGO
        $datos_pago = [
            'TipoCambioP' => $complemento->moneda === 'USD' ? $complemento->tipo_cambio : 1,
            'Monto' => round($monto_total, 2),
            'MonedaP' => $complemento->moneda,
            'FormaDePagoP' => $complemento->forma_pago->codigo,
            'FechaPago' => $complemento->fecha_pago->format("Y-m-d") . "T" . $complemento->fecha_pago->format("H:i:s")
        ];
        if ($complemento->forma_pago_id != 1) {
            if ($complemento->cuenta_origen) {
                $datos_pago['RfcEmisorCtaOrd'] = $complemento->cuenta_origen->banco->rfc;
                $datos_pago['CtaOrdenante'] = $complemento->cuenta_origen->cuenta;
            }
            if ($complemento->cuenta_destino) {
                $datos_pago['RfcEmisorCtaBen'] = $complemento->cuenta_destino->banco->rfc;
                $datos_pago['CtaBeneficiario'] = $complemento->cuenta_destino->cuenta;
            }
            $datos_pago['NumOperacion'] = $complemento->numero_operacion;
        }
        $Builder->setDatosPago($datos_pago);

        $con_impuestos = false;
        $con_retenciones = false;
        $importe_traslado_base = 0;
        $importe_traslado_impuesto = 0;
        $importe_retencion_base = 0;
        $importe_retencion_impuesto = 0;
        foreach ($complemento->facturas as $factura) {
            $Builder->resetAtributosDocRel();

            $subtotal = $factura->subtotal;
            $porciento_iva = $factura->porciento_iva;
            $porciento_ret_iva = $factura->porciento_ret_iva;
            $saldo_anterior = $factura->pivot->balance_previo;
            $importe_pagado = $factura->pivot->importe_pagado;
            $saldo_insoluto = $saldo_anterior - $importe_pagado;
            $saldo_insoluto = ($saldo_insoluto < 0.01 && $saldo_insoluto > -0.01) ? 0.00 : round($saldo_insoluto, 2);
            $Builder->setAtributoDocRel('NumParcialidad', $factura->pivot->no_parcialidad);
            $Builder->setAtributoDocRel('Serie', $factura->serie->descripcion);
            $Builder->setAtributoDocRel('Folio', $factura->folio_interno);
            $Builder->setAtributoDocRel('MonedaDR', $factura->moneda);
            $Builder->setAtributoDocRel('ImpSaldoInsoluto', $saldo_insoluto);
            $Builder->setAtributoDocRel('ImpSaldoAnt', round($saldo_anterior, 2));
            $Builder->setAtributoDocRel('ImpPagado', round($importe_pagado, 2));
            $Builder->setAtributoDocRel('IdDocumento', $factura->uuid);
            $Builder->setAtributoDocRel('ObjetoImpDR', $factura->porciento_iva > 0 ? '02' : '01');

            if ($porciento_iva > 0) {
                $con_impuestos = true;
                $importe_traslado_base += round($subtotal, 2);
                $importe_traslado_impuesto += round($subtotal * $porciento_iva / 100, 6);

                if ($porciento_ret_iva > 0) {
                    $con_retenciones = true;
                    $importe_retencion_base += round($subtotal, 2);
                    $importe_retencion_impuesto += round($subtotal * $porciento_ret_iva / 100, 6);
                    $nodo = ['nodo' => 'RetencionDR', 'BaseDR' => round($subtotal, 2), 'ImpuestoDR' => '002', 'TipoFactorDR' => 'Tasa', 'TasaOCuotaDR' => $Builder->fnumero($porciento_ret_iva / 100, 6), 'ImporteDR' => $Builder->fnumero($subtotal * $porciento_ret_iva / 100, 6)];
                    $Builder->addImpuestoDR('RetencionesDR', $nodo);
                }

                $nodo = ['nodo' => 'TrasladoDR', 'BaseDR' => round($subtotal, 2), 'ImpuestoDR' => '002', 'TipoFactorDR' => 'Tasa', 'TasaOCuotaDR' => $Builder->fnumero($porciento_iva / 100, 6), 'ImporteDR' => $Builder->fnumero($subtotal * $porciento_iva / 100, 6)];
                $Builder->addImpuestoDR('TrasladosDR', $nodo);
            }

            $Builder->addDocToDocs();
        }

        if ($con_retenciones) {
            $nodo = ['nodo' => 'RetencionP', 'ImpuestoP' => '002', 'ImporteP' => $Builder->fnumero($importe_retencion_impuesto, 6)];
            $Builder->addImpuestoP('RetencionesP', $nodo);

            $Builder->setTotalesPagos('TotalRetencionesIVA', round($importe_retencion_impuesto, 2));
        }

        if ($con_impuestos) {
            $TotalTrasladosBaseIVA16 = round($importe_traslado_base * $datos_pago['TipoCambioP'], 2);
            $nodo = ['nodo' => 'TrasladoP', 'BaseP' => $Builder->fnumero($importe_traslado_base, 6), 'ImpuestoP' => '002', 'TipoFactorP' => 'Tasa', 'TasaOCuotaP' => $Builder->fnumero($factura->porciento_iva / 100, 6), 'ImporteP' => $Builder->fnumero($importe_traslado_impuesto, 6)];
            $Builder->addImpuestoP('TrasladosP', $nodo);

            $Builder->setTotalesPagos('TotalTrasladosBaseIVA16', $TotalTrasladosBaseIVA16);
            $Builder->setTotalesPagos('TotalTrasladosImpuestoIVA16', round($importe_traslado_impuesto, 2));
        }

        ///////////////////////////////////////////////////////////////////////
        // GENERAR XML

        $xmlOrig = $Builder->generarXML();
        if ($xmlOrig == false) {
            throw new \Exception("Error al formar archivo XML ");
            //            return false;
        }

        //Guardamos el XML previo en disco
        //        $relative_path = config('app.cfdi_path') . "CP_$id/";
        //        $path = public_path() . $relative_path;
        //
        //        // dir doesn't exist, make it
        //        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
        //            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        //        }
        $name = 'CP_' . $id . '_PRE_' . date('YmdHis') . '.xml';
        //        $filename = $path . $name;
        //        file_put_contents($filename, $xmlOrig);
        Storage::disk('public')->put("cfdi/CP_$id/$name", $xmlOrig);

        ///////////////////////////////////////////////////////////////////////
        // CERTIFICACION CFDI

        $Timbrador = new CfdiTimbrador($propietrario);

        if ($this->modo_productivo) {
            $Timbrador->modo_productivo();
        } else {
            $Timbrador->modo_pruebas();
        }

        $complemento->fecha_emision = now();
        $result = $Timbrador->timbra($id, $xmlOrig);


        if ($result['res'] == 1) {

            //guardamos el codigo qr
            $name_qr = 'CP_' . $id . '_' . $result['uuid'] . 'codigoQr.jpg';
            //            $file_qr = $path . $name_qr;
            //            file_put_contents($file_qr, $result['codigoQr']);
            Storage::disk('public')->put("cfdi/CP_$id/$name_qr", $result['codigoQr']);
            $complemento->direccion_codigo_qr = "cfdi/CP_$id/$name_qr";

            //guardamos el xml timbrado
            $name_xml = 'CP_' . $id . '_' . $result['uuid'] . '.xml';
            //            $file_xml = $path . $name_xml;
            //            file_put_contents($file_xml, $result['xmlTimbrado']);
            Storage::disk('public')->put("cfdi/CP_$id/$name_xml", $result['xmlTimbrado']);
            $complemento->direccion_xml = "cfdi/CP_$id/$name_xml";

            //seteamos los campos faltantes en factura
            $complemento->uuid = $result['uuid'];
            $complemento->cadena_original = $result['cadenaOriginal'];
            $complemento->estado = 'TIMBRADA';

            //si esta en modo pruebas avisar en bd
            if ($this->modo_productivo == 0) {
                $complemento->modo_prueba_cfdi = 1;
            }

            //            $dir = $file_xml;
            $p = xml_parser_create("UTF-8");
            //            $var = file_get_contents($dir);
            $var = Storage::disk('public')->get("cfdi/CP_$id/$name_xml");
            xml_parse_into_struct($p, $var, $vals, $index);
            xml_parser_free($p);

            $complemento->fecha_certificacion = now();
            $complemento->numero_serie_sat = $vals[$index['TFD:TIMBREFISCALDIGITAL'][0]]['attributes']['NOCERTIFICADOSAT'];
            $complemento->cert_rfc_proveedor = $vals[$index['TFD:TIMBREFISCALDIGITAL'][0]]['attributes']['RFCPROVCERTIF'];
            $complemento->sello_digital_cfdi = $vals[$index['TFD:TIMBREFISCALDIGITAL'][0]]['attributes']['SELLOCFD'];
            $complemento->sello_digital_sat = $vals[$index['TFD:TIMBREFISCALDIGITAL'][0]]['attributes']['SELLOSAT'];
            $complemento->serie_certificado = $vals[$index['CFDI:COMPROBANTE'][0]]['attributes']['CERTIFICADO'];
            $complemento->numero_serie_emisor = $vals[$index['CFDI:COMPROBANTE'][0]]['attributes']['NOCERTIFICADO'];

            activity('Complemento Timbrado')
                ->on($complemento)
                ->withProperties($complemento->getDirty())
                ->log("Timbrado Complemento con folio interno: $complemento->folio_interno");
            $complemento->saveQuietly();
            $response = ['success' => true, 'message' => "Timbrado exitoso."];
        } else {
            $error = 'Error al timbrar: ' . $result['msg'];
            $response = ['success' => false, 'message' => $error];
        }

        return $response;
    }

    /**
     * @throws \Exception
     */
    public function cancelar($id, $motivo_cancelacion, $folio_sustituto = null)
    {
        date_default_timezone_set('America/Cancun');

        ///////////////////////////////////////////////////////////////////////
        //G E N E R A C I O N   D E   L A  F A C T U R A

        $factura = Factura::find($id);

        if (!$factura) {
            throw new \Exception('Factura no encontrada: ' . $id);
        }

        $uuid = $factura->uuid;
        $propietario = Sucursal::decryptInfo($factura->propietario);
        $Timbrador = new CfdiTimbrador($propietario);

        if (!$factura->modo_prueba_cfdi) {
            $Timbrador->modo_productivo();
            // return new JsonResponse(['message'=>'timbrado en modo productivo' . $uuid]);
        } else {
            // $Timbrador->modo_productivo();
            $Timbrador->modo_pruebas();
            // return new JsonResponse(['message'=>'timbrado en modo de pruebas' . $uuid]);
        }


        $motivo = MotivoCancelacionFactura::find($motivo_cancelacion);
        $folio = $motivo->id == 1 ? Factura::find($folio_sustituto)->uuid : '';
        if ($factura->modo_prueba_cfdi == 0) {
            $result = $Timbrador->cancela($uuid, $motivo->codigo, $folio);
        } else {
            //si la factura fue timbrada en pruebas cancelar en automatico
            $result = ['res' => 1];
        }


        if ($result['res'] === 1 || ($result['res'] === 0 && $result['msg'] === 'UUID Previamente cancelado.')) {

            $factura->estado = 'CANCELADA';
            $factura->motivo_cancelacion_id = $motivo_cancelacion;
            $factura->saveQuietly();

            $label = 'Factura';
            $data = [
                'motivo_cancelacion_id' => $motivo_cancelacion,
                'motivo_cancelacion' => $motivo->nombre,
            ];
            activity($label . ' Cancelada')
                ->on($factura)
                ->withProperties($data)
                ->log("Cancelada $label con folio interno: $factura->folio_interno");
            $success = true;
            $message = 'Cancelación exitosa.';
        } else {
            $error = 'Error al cancelar: ' . $result['msg'];

            $success = false;
            $message = $error;
        }

        return [
            'success' => $success,
            'message' => $message
        ];
    }
}
