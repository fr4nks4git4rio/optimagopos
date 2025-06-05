<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SOAP\BaseSoapController;
use App\Http\Controllers\SOAP\InstanceSoapClient;
use App\Models\Administracion\Traza;
use App\Models\Factura;
use App\Models\PanelPacSetting;
use App\Models\Cliente;
use App\Services\Timbrado\CfdiConstructor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests;

class SoapController extends BaseSoapController
{
    private $service;

    /**
     * SoapController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function establecer_modo()
    {
        $modo_productivo = system_config('cfdi_timbrado_productivo');

        if ($modo_productivo == 1) {
            self::modo_productivo();
        } else {
            self::modo_pruebas();
        }
        $this->service = InstanceSoapClient::init();
        return $this;
    }
    public function obtenerTimbresDisponibles($rfc)
    {
        $this->setRfcEmisor($rfc);
        $this->establecer_modo();
        if (is_array($this->service)) {
            $response = ['success' => false, 'message' => $this->service['message']];
            return json_encode($response);
        }
        try {
            $params = [
                'usuarioIntegrador' => self::getUsuarioIntegrador(),
                'rfcEmisor' => self::getRfcEmisor()
            ];
            $response = $this->service->ObtieneTimbresDisponibles($params);
        } catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        /*Obtenemos resultado del response*/
        $tipoExcepcion = $response->ObtieneTimbresDisponiblesResult->anyType[0];
        $numeroExcepcion = $response->ObtieneTimbresDisponiblesResult->anyType[1];
        $msg = $response->ObtieneTimbresDisponiblesResult->anyType[2];
        $asignados = $response->ObtieneTimbresDisponiblesResult->anyType[3];
        $utilizados = $response->ObtieneTimbresDisponiblesResult->anyType[4];
        $disponibles = $response->ObtieneTimbresDisponiblesResult->anyType[5];

        if ($numeroExcepcion == "0") {
            $response = ['success' => true, 'message' => "Consulta exitosa", 'disponibles' => $disponibles];
        } else {
            $response = ['success' => false, 'message' => $msg];
        }

        return $response;
    }
}
