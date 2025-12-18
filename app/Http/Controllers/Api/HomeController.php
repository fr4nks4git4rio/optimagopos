<?php

namespace App\Http\Controllers\Api;

use App\Models\Administracion\CodificadoresGenerales\PuntoRuta;
use App\Models\API\CargaObject;
use App\Models\API\GlobalSiteValues;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Terminal;
use App\Models\Ticket;
use App\Models\TicketProductoCorreccion;
use App\Models\TipoCambio;
use App\Models\User;
use App\Models\Ventas\OrdenServicio;
use App\Models\Ventas\OrdenServicioEvidencia;
use App\Models\Ventas\OrdenServicioImagen;
use App\Notifications\SiteNotification;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HomeController
{
    public function parseTicketJson(Request $request)
    {
        // Paso 1: Obtener contenido crudo
        $raw = $request->getContent();

        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true); // <- ahora sí tenés el array
        }

        Log::error($decoded);

        // Paso 3: Verificar si se decodificó correctamente
        if (!$decoded || !isset($decoded['Items'])) {
            return response()->json(['success' => false, 'error' => 'JSON inválido o incompleto'], 400);
        }

        // dd(Carbon::parse($decoded['TransactionStartTime'])->format('Y-m-d H:i:s'));

        $terminal = Terminal::findByIdentificador($decoded['APIUserName']);

        if (!$terminal) {
            return response()->json(['success' => false, 'error' => 'Terminal no encontrada'], 400);
        }
        // Paso 4: Acceder a datos generales
        $clerk = Empleado::where('sucursal_id', $terminal->sucursal_id)->where('id_empleado', $decoded['ClerkId'])->first();
        if (!$clerk) {
            $clerk = Empleado::create([
                'id_empleado' => $decoded['ClerkId'],
                'nombre' => $decoded['ClerkName'] ? Crypt::encrypt($decoded['ClerkName']) : '',
                'sucursal_id' => $terminal->sucursal_id
            ]);
        }
        $comensal = null;
        if (isset($decoded['CustomerFiscalId']) && $decoded['CustomerFiscalId']) {
            $comensal = Cliente::where('rfc', $decoded['CustomerFiscalId'])->get()->first();
            if (!$comensal) {
                $comensal = Cliente::create([
                    'rfc' => $decoded['CustomerFiscalId'],
                    'es_comensal' => 1
                ]);
            } else {
                $comensal->es_comensal = 1;
                $comensal->save();
            }
        }

        $items = $decoded['Items'] ?? [];

        $vigencia_facturacion = null;
        switch ($terminal->sucursal->tipo_vigencia_ticket_facturacion) {
            case 'last_day_month':
                $vigencia_facturacion = today()->copy()->lastOfMonth();
                break;
            case 'days_number_after_emitted':
                $vigencia_facturacion = today()->copy()->addDays($terminal->sucursal->dias_vigencia);
                break;
            case 'days_number_next_month':
                $vigencia_facturacion = today()->copy()->addMonth()->setDay($terminal->sucursal->dias_vigencia);
                break;
        }

        $tipo_cambio = get_tipo_cambio(null, $terminal->sucursal->cliente_id);
        if (!$tipo_cambio->id) {
            $tipo_cambio = TipoCambio::obtenerTipoCambioUrl($terminal->sucursal->cliente_id);
            if (is_string($tipo_cambio)) {
                return response()->json(['success' => false, 'error' => $tipo_cambio], 400);
            }
        }
        $ticket = Ticket::create([
            'ubicacion' => $decoded['Location'] ?? '',
            'id_transaccion' => $decoded['TransactionId'],
            'fecha_transaccion' => Carbon::createFromFormat('d/m/Y H:i:s', $decoded['TransactionStartTime'])->format('Y-m-d H:i:s'),
            'vigencia_facturacion' => $vigencia_facturacion->format('Y-m-d'),
            'empleado_id' => $clerk->id,
            'sucursal_id' => $terminal->sucursal_id,
            'terminal_id' => $terminal->id,
            'comensal_id' => $comensal ? $comensal->id : null,
            'tipo_cambio' => $tipo_cambio->tasa
        ]);

        // Ejemplo de lógica condicional por tipo de ítem
        $correccion = null;
        $importe = 0;
        foreach ($items as $item) {
            $type = $item['Type'] ?? 'Product';

            if ($type === 'Tax') {
                $ticket->impuestos()->create([
                    'nombre' => $item['Name'],
                    'monto' => $item['Amount']
                ]);
            }

            if ($type === 'Tender') {
                $forma_pago = DB::table('tb_sucursal_forma_pagos')
                    ->where('sucursal_id', $terminal->sucursal_id)
                    ->where('nombre', $item['Name'])
                    ->get()->first();
                // Guardar método de pago en tabla pagos
                $ticket->operaciones()->create([
                    'nombre' => $item['Name'] ?? '',
                    'monto' => $item['Amount'] ?? '',
                    'propina' => $item['Tip'] != '' && (float)$item['Tip'] > 0 ? (float)$item['Tip'] : 0,
                    'empleado_id' => $item['Tip'] != '' && (float)$item['Tip'] > 0 ? $clerk->id : null,
                    'sucursal_forma_pago_id' => optional($forma_pago)->id
                ]);
            }

            if ($type === 'Correction') {
                $correccion = new TicketProductoCorreccion();
                $correccion->nombre = $item['name'];
                $correccion->save();
            }

            if ($type === 'Product') {
                $producto = Producto::where('sucursal_id', $terminal->sucursal_id)
                    ->where('id_producto', $item['Id'])
                    ->first();
                if (!$producto) {
                    $producto = Producto::create([
                        'id_producto' => $item['Id'],
                        'nombre' => $item['Name'],
                        'precio' => $item['Amount'] / $item['Qty'],
                        'sucursal_id' => $terminal->sucursal_id
                    ]);
                }
                $departamento = Departamento::where('sucursal_id', $terminal->sucursal_id)
                    ->where('id_departamento', $item['DepartmentId'])
                    ->first();
                if (!$departamento) {
                    $departamento = Departamento::create([
                        'id_departamento' => $item['DepartmentId'],
                        'nombre' => $item['DepartmentName'],
                        'sucursal_id' => $terminal->sucursal_id
                    ]);
                }

                if ($correccion) {
                    $prod = $ticket->productos()->where('producto_id', $producto->id)
                        ->where('departamento_id', $departamento->id)
                        ->first();
                    $correccion->producto_id = optional($prod)->id;
                    $correccion->cantidad = abs($item['Qty']);
                    $correccion->precio = abs($item['Amount']);
                    $correccion->save();
                    $correccion = null;
                    continue;
                } else {
                    $ticketProducto = $ticket->productos()->create([
                        'producto_id' => $producto->id,
                        'departamento_id' => $departamento->id,
                        'precio' => $item['Amount'] ?? 0,
                        'cantidad' => $item['Qty'] ?? 0,
                        'descuento' => $item['Discount'] ?? 0,
                    ]);
                    $importe += $ticketProducto->precio - $ticketProducto->descuento;
                }
            }
        }
        $ticket->importe = $importe;
        $ticket->save();

        return response()->json(['success' => true]);
    }
}
