<?php

namespace App\Console\Commands;

use App\Models\ClaveProdServ;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Factura;
use App\Models\Log;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\Terminal;
use App\Models\Ticket;
use App\Models\TicketProducto;
use App\Models\TicketProductoCorreccion;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class GenerarFacturasPeriodicasSuscripciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generar-fact-periodicas-suscripciones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $suscripciones = Suscripcion::where('estado', 'ACTIVA')
            ->whereDate('fecha_inicio_pagos', '<=', today())
            ->get();

        foreach ($suscripciones as $suscripcion) {
            $fechaPago = Carbon::parse($suscripcion->fecha_inicio_pagos);
            while (
                ($fechaPago->isBefore(today()) || $fechaPago->isSameDay(today()))
                && DB::table('tb_facturas')->where('suscripcion_id', $suscripcion->id)->whereDate('fecha_pago', $fechaPago)->doesntExist()
            ) {

                $this->info("Generando factura para la suscripción ID: {$suscripcion->id}");

                $factura = new Factura();
                $factura->estado = 'PRECAPTURADA';
                $factura->fecha_pago = $fechaPago;
                $factura->fecha_emision = today();
                $factura->moneda = system_config('moneda_sistema');
                $factura->tipo_cambio = get_tipo_cambio_sistema(today()->format('Y-m-d'))->tasa;
                $factura->cliente_id = $suscripcion->cliente_id;
                $factura->serie_id = 1;
                $factura->forma_pago_id = 5;
                $factura->cfdi_id = null;
                $factura->metodo_pago_id = 2;
                $factura->tipo_comprobante_id = 1;
                $factura->porciento_iva = system_iva();
                $factura->propietario_id = get_system_owner()->id;
                $factura->comentarios = "Suscripción " . $suscripcion->paquete->nombre;
                $factura->suscripcion_id = $suscripcion->id;
                $subtotal = round($suscripcion->total * $suscripcion->multiplicador_periodicidad, 2);
                $factura->subtotal = $subtotal;
                $iva = round($subtotal * (system_iva() / 100), 2);
                $factura->iva = $iva;
                $factura->total = $subtotal + $iva;
                $factura->cantidad_letras = convertir_numero_a_letras(round($factura->total, 2), $factura->moneda);
                $factura->del_sistema = 1;
                $factura->save();

                $factura->folio_interno = $factura->serie?->descripcion . $factura->id;
                $factura->save();

                $claveProdServ = ClaveProdServ::where('codigo', '81112501')->first();
                $factura->factura_conceptos()->create([
                    'cantidad' => 1,
                    'precio_unitario' => $subtotal,
                    'descripcion' => "Suscripción " . $suscripcion->paquete->nombre,
                    'clave_unidad_id' => $claveProdServ?->clave_unidad_id,
                    'clave_prod_serv_id' => $claveProdServ?->id,
                    'objeto_impuesto_id' => 2,
                    'suscripcion_id' => $suscripcion->id
                ]);

                $fechaPago->addMonths($suscripcion->multiplicador_periodicidad);
            }
        }
    }
}
