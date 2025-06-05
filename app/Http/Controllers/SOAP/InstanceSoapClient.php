<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 4/25/2019
 * Time: 10:00 PM
 */

namespace App\Http\Controllers\SOAP;

use SoapClient;

class InstanceSoapClient extends BaseSoapController
{
    public static function init()
    {
        try {
            $wsdlUrl = self::getWsdl();
            $soapClientOptions = [
                'cache_wsdl' => WSDL_CACHE_NONE,
                'trace' => 1,
                'stream_context' => stream_context_create(
                    [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ]
                )
            ];

            libxml_disable_entity_loader(false);
            return new SoapClient($wsdlUrl, $soapClientOptions);
        } catch (\SoapFault $e) {
            \Log::error($e->getMessage());
            return ['message' => $e->getMessage()];
        }

    }
}
