<?php

namespace App\Http\Controllers\SOAP;

use App\Http\Controllers\Helpers\Helper;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class BaseSoapController extends Controller
{
  protected static $options;
  protected static $context;
  protected static $wsdl;

  protected static $usuarioIntegrador = '';
  protected static $rfcEmisor = '';

  protected static $test_url_ws = "https://cfdi33-pruebas.buzoncfdi.mx:1443/Timbrado.asmx?wsdl";
  protected static $test_usuarioIntegrador = 'mvpNUXmQfK8=';
  protected static $test_rfcEmisor = 'AAA010101AAA';

  protected static $prod_url_ws = "https://timbracfdi33.mx:1443/Timbrado.asmx?WSDL";
  protected static $prod_usuarioIntegrador = '';

  protected static $modo_productivo = false;

  /**
   * BaseSoapController constructor.
   */
  public function __construct()
  {
  }

  /**
   * @return mixed
   */
  public static function getWsdl()
  {
    return self::$wsdl;
  }

  /**
   * @param mixed $wsdl
   * @return mixed $wsdl
   */
  public static function setWsdl($wsdl)
  {
    return self::$wsdl = $wsdl;
  }

  /**
   * @return string
   */
  public static function getUsuarioIntegrador()
  {
    return self::$usuarioIntegrador;
  }

  /**
   * @param string $usuarioIntegrador
   * @return string $usuarioIntegrador
   */
  public static function setUsuarioIntegrador($usuarioIntegrador)
  {
    return self::$usuarioIntegrador = $usuarioIntegrador;
  }

  /**
   * @return string
   */
  public static function getRfcEmisor()
  {
    return self::$rfcEmisor;
  }

  /**
   * @param string $rfcEmisor
   * @return string $rfcEmisor
   */
  public static function setRfcEmisor($rfcEmisor)
  {
    return self::$rfcEmisor = $rfcEmisor;
  }

  /**
   * @return bool
   */
  public static function isModoProductivo()
  {
    return self::$modo_productivo;
  }

  /**
   * @return string
   */
  public static function getTestUrlWs()
  {
    return self::$test_url_ws;
  }

  /**
   * @return string
   */
  public static function getTestUsuarioIntegrador()
  {
    return self::$test_usuarioIntegrador;
  }

  /**
   * @return string
   */
  public static function getTestRfcEmisor()
  {
    return self::$test_rfcEmisor;
  }

  /**
   * @return string
   */
  public static function getProdUrlWs()
  {
    return self::$prod_url_ws;
  }

  /**
   * @return string
   */
  public static function getProdUsuarioIntegrador()
  {
    return get_owner()->usuario_integrador_sat;
  }

  /**
   * @param bool $modo_productivo
   * @return bool $modo_productivo
   */
  public static function setModoProductivo($modo_productivo)
  {
    return self::$modo_productivo = $modo_productivo;
  }

  /**
   * Metodo para establecer los parametros de prueba
   */
  public static function modo_pruebas()
  {
    self::setWsdl(self::getTestUrlWs());
    self::setUsuarioIntegrador(self::getTestUsuarioIntegrador());
    self::setRfcEmisor(self::getTestRfcEmisor());
    self::setModoProductivo(false);
  }


  /**
   * Metodo para establecer los parametros de produccion
   */
  public function modo_productivo()
  {
    self::setWsdl(self::getProdUrlWs());
    self::setUsuarioIntegrador(self::getProdUsuarioIntegrador());
    self::setModoProductivo(true);
  }

  protected static function generateContext()
  {
    self::$options = [
      'http' => [
        'user_agent' => 'PHPSOAPClient'
      ]
    ];
    return self::$context = stream_context_create(self::$options);
  }
}
