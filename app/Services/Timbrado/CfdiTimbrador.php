<?php

// use SoapClient;


namespace App\Services\Timbrado;

use App\Models\Cliente;
use App\Models\Sucursal;

class  CfdiTimbrador
{
    // Define los datos de control
    private $emisor;
    private $url_ws = "";
    private $usuarioIntegrador = '';
    private $passwordIntegrador = '';
    private $rfcEmisor = '';
    private $test_url_ws = "https://pruebas.timbracfdi33.mx/Timbrado.asmx?wsdl";
    private $test_usuarioIntegrador = 'mvpNUXmQfK8=';
    private $test_passwordIntegrador = 'timbrado.SF.16672';
    private $test_rfcEmisor = 'AAA010101AAA';
    private $prod_url_ws = "https://timbracfdi33.mx:1443/Timbrado.asmx?wsdl";
    private $prod_usuarioIntegrador = 'K7Ta3yDV+/eu5CaVXx75/A==';
    private $prod_passwordIntegrador = 'K7Ta3yDV+/eu5CaVXx75/A==';
    // private $prod_usuarioIntegrador = 'NotRealString=';
    private $prod_rfcEmisor = 'CCT880315856';

    private $modo_productivo = false;

    // Define los datos de control

    function __construct(Sucursal $propietario)
    {
        $this->emisor = $propietario;
        $this->modo_pruebas();
    }

    /**
     * Metodo que devuelve el valor del RFC Emisor
     * @return string
     */
    public function getEmisor()
    {
        return $this->rfcEmisor;
    }

    /**
     * Metodo que devuelve el valor de la url_ws
     * @return string
     */
    public function getWsUrl()
    {
        return $this->url_ws;
    }

    /**
     * Metodo que cambia el ws para cancelar de otro pac
     */
    public function modo_cancelacion_foranea(): CfdiTimbrador
    {
        $this->url_ws = "https://www.timbracfdi.mx/servicioIntegracion/Timbrado.asmx?WSDL";
        return $this;
    }


    /**
     * Metodo para establecer los parametros de prueba
     */
    public function modo_pruebas()
    {
        $this->url_ws = $this->test_url_ws;
        $this->usuarioIntegrador = $this->test_usuarioIntegrador;
        $this->passwordIntegrador = $this->test_passwordIntegrador;
        $this->rfcEmisor = $this->test_rfcEmisor;
        return $this;
    }


    /**
     * Metodo para establecer los parametros de produccion
     */
    public function modo_productivo()
    {
        $this->url_ws = $this->prod_url_ws;
        $this->usuarioIntegrador = $this->prod_usuarioIntegrador;
        $this->passwordIntegrador = $this->prod_passwordIntegrador;

        $this->modo_productivo = true;
        $this->rfcEmisor =$this->emisor->rfc;
        return $this;
    }

    /**
     * Metodo que invoca las funciones de canelacion un timbre de acuerdo a su UUID
     * @param string $uuid
     * @return array
     */
    public function cancela($uuid = '', $motivo = '', $folio = '')
    {
        // Guarda el tiempo de inicio de la operacion
        $inicio = date("Y-m-d h:i:s");
        $time_inicio = microtime(true);

        // Define la liga de conexion al webservice
        $ws = $this->url_ws;
        $response = '';

        /*Folio fiscal(UUID) del comprobante a obtener, deberá ser uno válido de los que hayamos timbrado previamente*/
        $folioUUID = strtoupper($uuid);

        try {
            $params = array();
            /*Nombre del usuario integrador asignado, para efecto de pruebas utilizaremos 'mvpNUXmQfK8='*/
            $params['usuarioIntegrador'] = $this->usuarioIntegrador;
            /* Rfc emisor que emitió el comprobante*/
            $params['rfcEmisor'] = $this->rfcEmisor;
            /*Folio fiscal del comprobante a cancelar*/
            $params['folioUUID'] = $folioUUID;

            $params['motivoCancelacion'] = $motivo;

            if ($motivo === '01')
                $params['folioUUIDSustitucion'] = $folio;

            $context = stream_context_create(array(
                'ssl' => array(
                    // set some SSL/TLS specific options
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => $this->modo_productivo ? false : true  //--> solamente true en ambiente de pruebas
                ),
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                )
            ));
            $options = array();
            $options['stream_context'] = $context;
            $options['trace'] = true;

            $client = new \SoapClient($ws, $options);
//            $response = $client->__soapCall('CancelaCFDI', array('parameters' => $params));
            $response = $client->__soapCall('CancelaCFDI40', array('parameters' => $params));
        } catch (\SoapFault $fault) {
            $tiempo = microtime(true) - $time_inicio;
            return array('res' => 0, 'inicio' => $inicio, 'tiempo' => $tiempo, 'msg' => "SOAPFault: " . $fault->faultcode . "-" . $fault->faultstring);
        }

        /*Obtenemos resultado del response*/
        $tipoExcepcion = $response->CancelaCFDI40Result->anyType[0];
        $numeroExcepcion = $response->CancelaCFDI40Result->anyType[1];
//        $descripcionResultado = $response->CancelaCFDI40Result->anyType[2];
        $xmlTimbrado = $response->CancelaCFDI40Result->anyType[3];
        $codigoQr = $response->CancelaCFDI40Result->anyType[4];
        $descripcionResultado = $response->CancelaCFDI40Result->anyType[7];

        // Determina el final de la operacion
        $fin = date("Y-m-d h:i:s");
        $tiempo = microtime(true) - $time_inicio;


        if ($numeroExcepcion == "0") {
            /*El comprobante fue cancelado exitosamente*/
            return [
                'res' => 1, 'inicio' => $inicio, 'tiempo' => $tiempo, 'msg' => "El comprobante fue cancelado correctamente"
            ];
        } else {
            return [
                'res' => 0, 'inicio' => $inicio, 'tiempo' => $tiempo, 'msg' => $descripcionResultado
            ];
        }
    }

    /**
     * Metodo que invoca las funciones de consulta de un UUID
     * @param string $uuid
     * @return array
     */
    public function consulta($uuid = '')
    {
        // Guarda el tiempo de inicio de la operacion
        $inicio = date("Y-m-d h:i:s");
        $time_inicio = microtime(true);

        // Define la liga de conexion al webservice
        $ws = $this->url_ws;
        $response = '';

        /*Folio fiscal(UUID) del comprobante a obtener, deberá ser uno válido de los que hayamos timbrado previamente en pruebas*/
        $folioUUID = strtoupper($uuid);

        try {
            $params = array();
            /*Nombre del usuario integrador asignado, para efecto de pruebas utilizaremos 'mvpNUXmQfK8='*/
            $params['usuarioIntegrador'] = $this->usuarioIntegrador;
            /* Rfc emisor que emitió el comprobante*/
            $params['rfcEmisor'] = $this->rfcEmisor;
            /*Folio fiscal del comprobante a cancelar*/
            $params['folioUUID'] = $folioUUID;

            $context = stream_context_create(array(
                'ssl' => array(
                    // set some SSL/TLS specific options
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => $this->modo_productivo ? false : true  //--> solamente true en ambiente de pruebas
                ),
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                )
            ));
            $options = array();
            $options['stream_context'] = $context;
            $options['trace'] = true;

            $client = new \SoapClient($ws, $options);
            //$response = $client->__soapCall('CancelaCFDIAck', array('parameters' => $params));
            $response = $client->__soapCall('ObtieneCFDI', array('parameters' => $params));

        } catch (\SoapFault $fault) {
            $tiempo = microtime(true) - $time_inicio;
            return [
                'res' => 0, 'inicio' => $inicio, 'tiempo' => $tiempo, 'msg' => "SOAPFault: " . $fault->faultcode . "-" . $fault->faultstring
            ];
        }

        var_dump($response);

        /*Obtenemos resultado del response*/
        $tipoExcepcion = $response->CancelaCFDIResult->anyType[0];
        $numeroExcepcion = $response->CancelaCFDIResult->anyType[1];
        $descripcionResultado = $response->CancelaCFDIResult->anyType[2];
        $xmlTimbrado = $response->CancelaCFDIResult->anyType[3];
        $codigoQr = $response->CancelaCFDIResult->anyType[4];
        $cadenaOriginal = $response->CancelaCFDIResult->anyType[5];

        // Determina el final de la operacion
        $fin = date("Y-m-d h:i:s");
        $tiempo = microtime(true) - $time_inicio;


        if ($numeroExcepcion == "0") {
            /*El comprobante fue cancelado exitosamente*/
            return [
                'res' => 1, 'inicio' => $inicio, 'tiempo' => $tiempo, 'msg' => "El comprobante fue cancelado correctamente"
            ];

        } else {
            return [
                'res' => 0, 'inicio' => $inicio, 'tiempo' => $tiempo, 'msg' => $descripcionResultado
            ];
        }
    }

    /**
     * Metodo que timbra un xml recibe el ID y el XML
     * @param $id_reg
     * @param $xml
     * @return array
     */
    public function timbra($id_reg, $xml, $es_carta_porte = false)
    {
        // Guarda el tiempo de inicio de la operacion
        $inicio = date("Y-m-d h:i:s");
        $time_inicio = microtime(true);

        // Define la liga de conexion al webservice
        $ws = $this->url_ws;

        /*Genera la marca del xml*/
        $base64Comprobante = base64_encode($xml);

        try {
            $params = array();
            /*Nombre del usuario integrador asignado, para efecto de pruebas utilizaremos 'mvpNUXmQfK8='*/
            $params['usuarioIntegrador'] = $this->usuarioIntegrador;
            /* Comprobante en base 64*/
            $params['xmlComprobanteBase64'] = $base64Comprobante;

            /*Id del comprobante, deberá ser un identificador único, para efecto del ejemplo se utilizará un numero aleatorio*/
            $params['idComprobante'] = $id_reg;

            $context = stream_context_create(array(
                'ssl' => array(
                    // set some SSL/TLS specific options
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => $this->modo_productivo ? false : true  //--> solamente true en ambiente de pruebas
                ),
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                )
            ));
            $options = array();
            $options['stream_context'] = $context;
            $options['cache_wsdl'] = 'WSDL_CACHE_MEMORY';
            $options['trace'] = true;

            libxml_disable_entity_loader(false);

            $client = new \SoapClient($ws, $options);
            $response = $client->__soapCall('TimbraCFDI', array('parameters' => $params));

        } catch (\SoapFault $fault) {
            $tiempo = microtime(true) - $time_inicio;
            return ['res' => 0, 'inicio' => $inicio, 'tiempo' => $tiempo, 'msg' => "SOAPFault: " . $fault->faultcode . "-" . $fault->faultstring, 'uuid' => '', 'obj' => ''];
        }

        /*Obtenemos resultado del response*/
        $tipoExcepcion = $response->TimbraCFDIResult->anyType[0];
        $numeroExcepcion = $response->TimbraCFDIResult->anyType[1];
        $descripcionResultado = $response->TimbraCFDIResult->anyType[2];
        $xmlTimbrado = $response->TimbraCFDIResult->anyType[3];
        $codigoQr = $response->TimbraCFDIResult->anyType[4];
        $cadenaOriginal = $response->TimbraCFDIResult->anyType[5];
        $errorInterno = $response->TimbraCFDIResult->anyType[6];
        $mensajeInterno = $response->TimbraCFDIResult->anyType[7];
        $res = $response->TimbraCFDIResult->anyType[8];

        $tiempo = microtime(true) - $time_inicio;

        if ($xmlTimbrado == '') {
            return ['res' => 0, 'inicio' => $inicio, 'tiempo' => $tiempo,
                'msg' => "Error al timbrar: " . "[" . $tipoExcepcion . "  " . $numeroExcepcion . " " . $descripcionResultado . "  ei=" . $errorInterno . " mi=" . $mensajeInterno . "]. | " . $response->TimbraCFDIResult->anyType[8],
                'uuid' => '', 'obj' => $response];
        }

        $res_array = json_decode($res, true);
        $uuid = $es_carta_porte ? $res_array[1]['Value'] : $res_array[0]['Value'];

        // Determina el final de la operacion
        $fin = date("Y-m-d h:i:s");


        // print_r($response);

        /*El comprobante fue timbrado exitosamente*/
        return ['res' => 1, 'inicio' => $inicio, 'tiempo' => $tiempo,
            'msg' => "El comprobante fue timbrado correctamente", 'xmlTimbrado' => $xmlTimbrado,
            'codigoQr' => $codigoQr, 'cadenaOriginal' => $cadenaOriginal, 'uuid' => $uuid,
            'obj' => $response];
    }


    /**
     * Metodo que invoca las funciones de consulta de un UUID
     * @param string $uuid
     * @return array
     */
    public function consultaTimbresDisponibles()
    {

        // Guarda el tiempo de inicio de la operacion
        $inicio = date("Y-m-d h:i:s");
        $time_inicio = microtime(true);

        // Define la liga de conexion al webservice
        $ws = $this->url_ws;
        $response = '';

        try {
            $params = array();
            /*Nombre del usuario integrador asignado, para efecto de pruebas utilizaremos 'mvpNUXmQfK8='*/
            $params['usuarioIntegrador'] = $this->usuarioIntegrador;
            /* Rfc emisor que emitió el comprobante*/
            $params['rfcEmisor'] = $this->rfcEmisor;

            $context = stream_context_create(array(
                'ssl' => array(
                    // set some SSL/TLS specific options
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => $this->modo_productivo ? false : true  //--> solamente true en ambiente de pruebas
                ),
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                )
            ));
            $options = array();
            $options['stream_context'] = $context;
            $options['trace'] = true;

            $client = new \SoapClient($ws, $options);
            $response = $client->__soapCall('ObtieneTimbresDisponibles', array('parameters' => $params));

        } catch (\SoapFault $fault) {
            $tiempo = microtime(true) - $time_inicio;
            return [
                'res' => 0, 'inicio' => $inicio, 'tiempo' => $tiempo, 'msg' => "SOAPFault: " . $fault->faultcode . "-" . $fault->faultstring
            ];
        }

        // echo var_dump($response);

        /*Obtenemos resultado del response*/
        $tipoExcepcion = $response->ObtieneTimbresDisponiblesResult->anyType[0];
        $numeroExcepcion = $response->ObtieneTimbresDisponiblesResult->anyType[1];
        $asignados = $response->ObtieneTimbresDisponiblesResult->anyType[3];
        $utilizados = $response->ObtieneTimbresDisponiblesResult->anyType[4];
        $disponibles = $response->ObtieneTimbresDisponiblesResult->anyType[5];

        // Determina el final de la operacion
        $fin = date("Y-m-d h:i:s");
        $tiempo = microtime(true) - $time_inicio;

        if ($numeroExcepcion == "0") {
            //consulta exitosa
            return [
                'res' => 1, 'inicio' => $inicio, 'tiempo' => $tiempo, 'disponibles' => $disponibles
            ];
        } else {

            return [
                'res' => 0, 'inicio' => $inicio, 'tiempo' => $tiempo, 'response' => $response, 'msg' => 'Error al consultar PAC'
            ];
        }
    }
}
