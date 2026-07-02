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
use App\Models\Sucursal;
use Illuminate\Support\Facades\Log;

class SoapController extends BaseSoapController
{
    /**
     * SoapController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function obtenerTimbresDisponibles($rfc)
    {
        if (user()->cliente_id) {
            $owner = Sucursal::where('rfc', $rfc)->first();
            $modo_productivo = $owner->cfdi_timbrado_productivo;
        } else {
            $owner = Cliente::where('rfc', $rfc)->first();
            $modo_productivo = system_config('cfdi_timbrado_productivo');
        }

        Log::info("Obteniendo Timbres Disponibles para el RFC: {$rfc}, Modo Productivo: {$modo_productivo}");
        if ($modo_productivo == 1) {
            $this->setRfcEmisor($rfc);
            $this->setUsuarioIntegrador($owner->usuario_integrador_sat);
            self::modo_productivo();
        } else {
            self::modo_pruebas();
        }
        $service = InstanceSoapClient::init();

        if (is_array($service)) {
            $response = ['success' => false, 'message' => $service['message']];
            return json_encode($response);
        }
        try {
            $params = [
                'usuarioIntegrador' => self::getUsuarioIntegrador(),
                'rfcEmisor' => self::getRfcEmisor()
            ];
            $response = $service->ObtieneTimbresDisponibles($params);
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

        Log::info("Resultado de la consulta de timbres disponibles: " . json_encode($response));
        return $response;
    }
}
