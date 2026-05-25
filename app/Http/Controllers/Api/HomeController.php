<?php

namespace App\Http\Controllers\Api;

use App\Models\Administracion\CodificadoresGenerales\PuntoRuta;
use App\Models\API\CargaObject;
use App\Models\API\GlobalSiteValues;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Log as ModelsLog;
use App\Models\Producto;
use App\Models\Terminal;
use App\Models\Ticket;
use App\Models\TicketProducto;
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
        ModelsLog::create([
            'log' => 'Data recibida.',
            'data' => $decoded ? json_encode($decoded) : '',
            'status' => 200
        ]);

        // Paso 3: Verificar si se decodificó correctamente
        if (!$decoded || !isset($decoded['Items'])) {
            ModelsLog::create([
                'log' => 'Error. JSON inválido o incompleto',
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 400
            ]);
            return response()->json(['success' => false, 'error' => 'JSON inválido o incompleto'], 400);
        }

        // dd(Carbon::parse($decoded['TransactionStartTime'])->format('Y-m-d H:i:s'));

        $terminal = Terminal::findByIdentificador($decoded['MerchantFiscalId']);
        if (!$terminal) {
            ModelsLog::create([
                'log' => 'Error. Terminal no encontrada.',
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            return response()->json(['success' => false, 'error' => 'Terminal no encontrada'], 400);
        }

        $tipo_cambio = get_tipo_cambio(null, $terminal->sucursal->cliente_id);
        if (!$tipo_cambio->id) {
            $tipo_cambio = TipoCambio::obtenerTipoCambioUrl($terminal->sucursal->cliente_id);
            if (is_string($tipo_cambio)) {
                ModelsLog::create([
                    'log' => "Error. $tipo_cambio",
                    'status' => 400
                ]);
                return response()->json(['success' => false, 'error' => $tipo_cambio], 400);
            }
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
        $prevProduct = null;
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
                    'monto' => $item['Amount'] ?? 0,
                    'propina' => $item['Tip'] != '' && (float)$item['Tip'] > 0 ? (float)$item['Tip'] : 0,
                    'empleado_id' => $item['Tip'] != '' && (float)$item['Tip'] > 0 ? $clerk->id : null,
                    'sucursal_forma_pago_id' => optional($forma_pago)->id,
                    'es_cambio' => $prevProduct != null && $item['Amount'] < 0 ? 1 : 0
                ]);
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

                $qty = $item['Qty'] ? (float)$item['Qty'] : 0;
                $amount = $item['Amount'] ? (float)$item['Amount'] : 0;
                if ($amount > 0 || $qty > 0) {
                    $discount = $item['Discount'] ? (float)$item['Discount'] : 0;
                    $ticketProducto = $ticket->productos()->create([
                        'producto_id' => $producto->id,
                        'departamento_id' => $departamento->id,
                        'precio' => $amount,
                        'cantidad' => $qty,
                        'descuento' => $discount,
                    ]);
                    $importe += $amount - $discount;
                } else {
                    $ticketProducto = TicketProducto::where('ticket_id', $ticket->id)->where('producto_id', $producto->id)->where('departamento_id', $departamento->id)->first();
                    if ($ticketProducto) {
                        $ticketProducto->cantidad -= abs($qty);
                        $ticketProducto->precio -= abs($amount);
                        $importe -= abs($amount);
                    }
                }
                $prevProduct = $producto;
            }

            if ($type === 'Correction') {
                $qty = $item['Qty'] ? (float)$item['Qty'] : 0;
                $amount = $item['Amount'] ? (float)$item['Amount'] : 0;
                if ($qty < 0 || $amount < 0) {
                    $correccion = new TicketProductoCorreccion();
                    $correccion->nombre = $item['name'];
                    $correccion->producto_id = optional($prevProduct)->id;
                    $correccion->cantidad = abs($qty);
                    $correccion->precio = abs($amount);
                    $correccion->save();
                }
                $prevProduct = null;
            }
        }
        $ticket->importe = $importe;
        $ticket->save();

        return response()->json(['success' => true]);
    }
}
