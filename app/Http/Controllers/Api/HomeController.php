<?php

namespace App\Http\Controllers\Api;

use App\Models\Administracion\CodificadoresGenerales\PuntoRuta;
use App\Models\API\CargaObject;
use App\Models\API\GlobalSiteValues;
use App\Models\Cliente;
use App\Models\Cuarentena;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\ItemTicketVK;
use App\Models\Log as ModelsLog;
use App\Models\ModificadorVK;
use App\Models\Producto;
use App\Models\Terminal;
use App\Models\Ticket;
use App\Models\TicketProducto;
use App\Models\TicketProductoCorreccion;
use App\Models\TicketVK;
use App\Models\TipoCambio;
use App\Models\User;
use App\Models\Ventas\OrdenServicio;
use App\Models\Ventas\OrdenServicioEvidencia;
use App\Models\Ventas\OrdenServicioImagen;
use App\Notifications\SiteNotification;
use Carbon\Carbon;
use Exception;
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

        // Paso 3: Verificar si se decodificó correctamente
        if (
            !$decoded
            || !isset($decoded['Items'])
            || (!isset($decoded['TerminalId']) && !isset($decoded['MerchantFiscalId']) && !isset($decoded['APIUserName']))
            || !isset($decoded['PosId'])
            || !isset($decoded['ClerkId'])
            || !isset($decoded['ClerkName'])
            || !isset($decoded['TransactionId'])
            || !isset($decoded['TransactionStartTime'])
        ) {
            ModelsLog::create([
                'log' => 'Error. JSON inválido o incompleto',
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 400
            ]);

            Cuarentena::create([
                'texto' => 'JSON inválido o incompleto',
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 0
            ]);
            return response()->json(['success' => false, 'error' => 'JSON inválido o incompleto'], 400);
        }

        // dd(Carbon::parse($decoded['TransactionStartTime'])->format('Y-m-d H:i:s'));

        $terminalId = $decoded['TerminalId'] ?? $decoded['MerchantFiscalId'];

        $terminal = Terminal::findByIdentificador($terminalId);

        if (!$terminal) {
            $terminalId = $decoded['APIUserName'];
            $terminal = Terminal::findByIdentificador($terminalId);
        }

        if (!$terminal) {
            ModelsLog::create([
                'log' => 'Error. Terminal no encontrada.',
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => 'Terminal no encontrada.',
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 0
            ]);
            return response()->json(['success' => false, 'error' => 'Terminal no encontrada'], 400);
        }

        if ($terminal->es_vk) {
            ModelsLog::create([
                'log' => 'Error. La Terminal está reconocida como dispositivo de Video Kitchen.',
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => 'Error. La Terminal está reconocida como dispositivo de Video Kitchen.',
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'cliente_id' => $terminal->sucursal->cliente_id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'es_vk' => 0
            ]);
            return response()->json(['success' => false, 'error' => 'La Terminal está reconocida como dispositivo de Video Kitchen.'], 400);
        }

        if ($terminal->suscripcion->estado != 'ACTIVA') {
            ModelsLog::create([
                'log' => 'La terminal no pertenece a una Suscripción ACTIVA.',
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => 'La terminal no pertenece a una Suscripción ACTIVA.',
                'ip' => $request->ip(),
                'cliente_id' => $terminal->sucursal->cliente_id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 0
            ]);
            return response()->json(['success' => false, 'error' => 'Terminal no encontrada'], 400);
        }

        ModelsLog::create([
            'log' => 'Data recibida.',
            'data' => $decoded ? json_encode($decoded) : '',
            'status' => 200,
            'sucursal_id' => $terminal->sucursal_id
        ]);

        $terminal->id_pos = $decoded['PosId'];
        $terminal->save();

        DB::beginTransaction();

        try {
            // Paso 4: Acceder a datos generales
            if (!$decoded['ClerkId']) {
                ModelsLog::create([
                    'log' => 'Id de empleado no recibido.',
                    'data' => $decoded ? json_encode($decoded) : '',
                    'status' => 400,
                    'sucursal_id' => $terminal->sucursal_id
                ]);
                Cuarentena::create([
                    'texto' => 'Id de empleado no recibido.',
                    'ip' => $request->ip(),
                    'data' => $decoded ? json_encode($decoded) : '',
                    'cliente_id' => $terminal->sucursal->cliente_id,
                    'sucursal_id' => $terminal->sucursal_id,
                    'terminal_id' => $terminal->id,
                    'es_vk' => 0
                ]);
                return response()->json(['success' => false, 'error' => 'Empelado no enviado'], 400);
            }
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
                $terminal->sucursal->cliente->comensales()->attach($comensal->id);
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
                'vigencia_facturacion' => $vigencia_facturacion ? $vigencia_facturacion->format('Y-m-d') : null,
                'empleado_id' => optional($clerk)->id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'comensal_id' => $comensal ? $comensal->id : null
            ]);

            // Ejemplo de lógica condicional por tipo de ítem
            $correccion = null;
            $importe = 0;
            $prevProduct = null;
            foreach ($items as $item) {
                $type = $item['Type'] ?? 'Product';


                if ($type === 'Tax') {
                    if (!isset($item['Name']) || !isset($item['Amount'])) {
                        ModelsLog::create([
                            'log' => 'Propiedad no recibida en ítem Tax. Propiedades esperadas: Name y Amount.',
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => 'Propiedad no recibida en ítem Tax. Propiedades esperadas: Name y Amount.',
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        DB::rollBack();
                        return response()->json(['success' => false, 'error' => 'Propiedad no recibida en ítem Tax. Propiedades esperadas: Name y Amount.'], 400);
                    }
                    $ticket->impuestos()->create([
                        'nombre' => $item['Name'],
                        'monto' => $item['Amount']
                    ]);
                }

                if ($type === 'Tender') {
                    if (!isset($item['Name']) || !isset($item['Amount'])) {
                        ModelsLog::create([
                            'log' => 'Propiedad no recibida en ítem Tender. Propiedades esperadas: Name y Amount.',
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => 'Propiedad no recibida en ítem Tender. Propiedades esperadas: Name y Amount.',
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        DB::rollBack();
                        return response()->json(['success' => false, 'error' => 'Propiedad no recibida en ítem Tender'], 400);
                    }
                    $forma_pago = DB::table('tb_sucursal_forma_pagos')
                        ->where('sucursal_id', $terminal->sucursal_id)
                        ->where('nombre', $item['Name'])
                        ->whereNull('deleted_at')
                        ->get()->first();
                    if (!$forma_pago) {
                        ModelsLog::create([
                            'log' => 'Error. Forma de pago no encontrada.',
                            'data' => json_encode($decoded),
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);

                        Cuarentena::create([
                            'texto' => "Forma de pago no encontrada: {$item['Name']}.",
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        DB::rollBack();
                        return response()->json(['success' => false, 'error' => 'Forma de pago no encontrada'], 400);
                    }
                    $tasa_cambio = 1;
                    if ($forma_pago && $forma_pago->moneda_id != $terminal->sucursal->moneda_base_id) {
                        $tipo_cambio = get_tipo_cambio($forma_pago->moneda_id, $terminal->sucursal->moneda_base_id, $terminal->sucursal->id);
                        if ($tipo_cambio->id) {
                            $tasa_cambio = $tipo_cambio->tasa;
                        }
                    }
                    // Guardar método de pago en tabla pagos
                    $ticket->operaciones()->create([
                        'nombre' => $item['Name'] ?? '',
                        'monto' => $item['Amount'] ?? 0,
                        'propina' => $item['Tip'] != '' && (float)$item['Tip'] > 0 ? (float)$item['Tip'] : 0,
                        'empleado_id' => $item['Tip'] != '' && (float)$item['Tip'] > 0 ? optional($clerk)->id : null,
                        'sucursal_forma_pago_id' => optional($forma_pago)->id,
                        'es_cambio' => $prevProduct != null && $item['Amount'] < 0 ? 1 : 0,
                        'tipo_cambio' => $tasa_cambio
                    ]);
                }

                if ($type === 'Product') {
                    if (!isset($item['Id']) || !isset($item['Name']) || !isset($item['Amount']) || !isset($item['Qty'])) {
                        ModelsLog::create([
                            'log' => 'Propiedad no recibida en ítem Product. Propiedades esperadas: Id, Name, Amount, Qty, DepartmentId y DepartmentName.',
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => 'Propiedad no recibida en ítem Product. Propiedades esperadas: Id, Name, Amount, Qty, DepartmentId y DepartmentName.',
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        DB::rollBack();
                        return response()->json(['success' => false, 'error' => 'Propiedad no recibida en ítem Product'], 400);
                    }

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

                    $departamento = null;
                    if ($item['DepartmentId']) {
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
                    }

                    $qty = $item['Qty'] ? (float)$item['Qty'] : 0;
                    $amount = $item['Amount'] ? (float)$item['Amount'] : 0;
                    $discount = $item['Discount'] ? (float)$item['Discount'] : 0;
                    $ticketProducto = TicketProducto::where('ticket_id', $ticket->id)->where('producto_id', $producto->id)->where('departamento_id', $departamento?->id)->first();
                    if (!$ticketProducto) {
                        $ticketProducto = new TicketProducto();
                        $ticketProducto->ticket_id = $ticket->id;
                        $ticketProducto->producto_id = $producto->id;
                        $ticketProducto->departamento_id = $departamento?->id;
                        $ticketProducto->precio = 0;
                        $ticketProducto->cantidad = 0;
                        $ticketProducto->descuento = 0;
                    }
                    $ticketProducto->precio += $amount;
                    $ticketProducto->cantidad += $qty;
                    $ticketProducto->descuento += $discount;
                    $ticketProducto->save();

                    $importe += $amount - $discount;

                    $prevProduct = $producto;
                }

                if ($type === 'Correction') {
                    if (!isset($item['Name']) || !isset($item['Amount']) || !isset($item['Qty'])) {
                        ModelsLog::create([
                            'log' => 'Propiedad no recibida en ítem Correction. Propiedades esperadas: Name, Amount y Qty.',
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => 'Propiedad no recibida en ítem Correction. Propiedades esperadas: Name, Amount y Qty.',
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        DB::rollBack();
                        return response()->json(['success' => false, 'error' => 'Propiedad no recibida en ítem Correction'], 400);
                    }

                    $qty = $item['Qty'] ? (float)$item['Qty'] : 0;
                    $amount = $item['Amount'] ? (float)$item['Amount'] : 0;

                    $correccion = new TicketProductoCorreccion();
                    $correccion->nombre = $item['Name'];
                    $correccion->producto_id = optional($prevProduct)->id;
                    $correccion->cantidad = $qty;
                    $correccion->precio = $amount;
                    $correccion->ticket_id = $ticket->id;
                    $correccion->save();

                    $prevProduct = null;
                }
            }
            $ticket->importe = $importe;
            $ticket->save();

            DB::commit();
        } catch (Exception $e) {
            ModelsLog::create([
                'log' => "Error recibiendo ticket json. Error: {$e->getMessage()}",
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => "Error recibiendo ticket json. Error: {$e->getMessage()}",
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 0
            ]);
            Log::error("Error recibiendo ticket json. Error: {$e->getMessage()}");
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Error recibiendo ticket json'], 400);
        }

        return response()->json(['success' => true]);
    }

    public function parseTicketVKJson(Request $request)
    {
        $raw = $request->getContent();

        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        if (
            !$decoded
            || !isset($decoded['TerminalId'])
            || !isset($decoded['APIUserName'])
            || !isset($decoded['APIPassword'])
            || !isset($decoded['Data'])
            || !isset($decoded['Data']['items'])
            || !isset($decoded['Data']['OrderStatus'])
            || !isset($decoded['Data']['orderNumber'])
            || !isset($decoded['Data']['timestamp'])
        ) {
            ModelsLog::create([
                'log' => 'Error. JSON inválido o incompleto',
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 400
            ]);

            Cuarentena::create([
                'texto' => 'JSON inválido o incompleto',
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 1
            ]);
            return response()->json(['success' => false, 'error' => 'JSON inválido o incompleto'], 400);
        }

        $terminalId = $decoded['TerminalId'];

        $terminal = Terminal::findByIdentificador($terminalId);

        if (!$terminal) {
            ModelsLog::create([
                'log' => 'Error. Terminal no encontrada.',
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => 'Terminal no encontrada.',
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 1
            ]);
            return response()->json(['success' => false, 'error' => 'Terminal no encontrada'], 400);
        }

        if (!$terminal->es_vk) {
            ModelsLog::create([
                'log' => 'Error. La Terminal no está reconocida como dispositivo de Video Kitchen.',
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => 'Error. La Terminal no está reconocida como dispositivo de Video Kitchen.',
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'cliente_id' => $terminal->sucursal->cliente_id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'es_vk' => 1
            ]);
            return response()->json(['success' => false, 'error' => 'La Terminal no está reconocida como dispositivo de Video Kitchen.'], 400);
        }

        if ($terminal->suscripcion->estado != 'ACTIVA') {
            ModelsLog::create([
                'log' => 'La terminal no pertenece a una Suscripción ACTIVA.',
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => 'La terminal no pertenece a una Suscripción ACTIVA.',
                'ip' => $request->ip(),
                'cliente_id' => $terminal->sucursal->cliente_id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 1
            ]);
            return response()->json(['success' => false, 'error' => 'Terminal no encontrada'], 400);
        }

        ModelsLog::create([
            'log' => 'Data recibida.',
            'data' => $decoded ? json_encode($decoded) : '',
            'status' => 200,
            'sucursal_id' => $terminal->sucursal_id
        ]);

        $ticket_vk = TicketVK::where('terminal_id', $terminal->id)->where('id_transaccion', $decoded['Data']['orderNumber'])->first();
        if ($ticket_vk) {
            $ticket_vk->update([
                'estado' => $decoded['Data']['OrderStatus']
            ]);
            return response()->json(['success' => true]);
        }

        DB::beginTransaction();

        try {
            $data = $decoded['Data'];

            $empleado = null;
            if ($data['operator']) {
                $empleado = Empleado::where('sucursal_id', $terminal->sucursal_id)->where('id_empleado', $data['operator'])->first();
                if (!$empleado) {
                    $empleado = Empleado::create([
                        'id_empleado' => $data['operator'],
                        'nombre' => $data['pos'] ? Crypt::encrypt($data['pos']) : '',
                        'sucursal_id' => $terminal->sucursal_id
                    ]);
                }
            }

            $departamento = null;
            if ($data['LocationId']) {
                $departamento = Departamento::where('sucursal_id', $terminal->sucursal_id)
                    ->where('id_departamento', $data['LocationId'])
                    ->first();
                if (!$departamento) {
                    $departamento = Departamento::create([
                        'id_departamento' => $data['LocationId'],
                        'nombre' => $data['location'],
                        'sucursal_id' => $terminal->sucursal_id
                    ]);
                }
            }

            $ticketVK = TicketVK::create([
                'mesa' => isset($data['table']) && $data['table'] ? $data['table'] : '',
                'asiento' => isset($data['seat']) && $data['seat'] ? $data['seat'] : '',
                'fecha_transaccion' => $data['timestamp'],
                'estado' => $data['OrderStatus'],
                'id_transaccion' => $data['orderNumber'],
                'pos_ip' => $data['PosIpAddress'],
                'tiempo_resolver' => $data['TimeToResolve'],
                'porciento_alerta_estado' => $data['WarningStatusThresholdInPercent'],
                'empleado_id' => $empleado?->id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'departamento_id' => $departamento?->id,
            ]);

            $items = $data['items'] ?? [];
            foreach ($items as $item) {
                $itemItecketVK = new ItemTicketVK();
                $itemItecketVK->ticket_vk_id = $ticketVK->id;
                if (!isset($item['name'])) {
                    ModelsLog::create([
                        'log' => 'Propiedad no recibida en ítem. Propiedad esperada: name.',
                        'data' => json_encode($decoded),
                        'status' => 400,
                        'sucursal_id' => $terminal->sucursal_id
                    ]);

                    Cuarentena::create([
                        'texto' => 'Propiedad no recibida en ítem. Propiedad esperada: name.',
                        'ip' => $request->ip(),
                        'data' => $decoded ? json_encode($decoded) : '',
                        'terminal_id' => $terminal->id,
                        'sucursal_id' => $terminal->sucursal_id,
                        'cliente_id' => $terminal->sucursal->cliente_id,
                        'es_vk' => 1
                    ]);
                    DB::rollBack();
                    return response()->json(['success' => false, 'error' => 'Propiedad no recibida en ítem. Propiedad esperada: name.'], 400);
                }

                $regex = '/^\s*(\d+(?:[.,]\d+)?)\s+(.+?)\s*$/';
                if (preg_match($regex, $item['name'], $coincidencias)) {
                    $itemItecketVK->cantidad = $coincidencias[1];
                    $itemItecketVK->nombre = $coincidencias[2];
                } else {
                    ModelsLog::create([
                        'log' => "La propiedad 'name' no cumple con el formato esperado.",
                        'data' => json_encode($decoded),
                        'status' => 400,
                        'sucursal_id' => $terminal->sucursal_id
                    ]);

                    Cuarentena::create([
                        'texto' => "La propiedad 'name' no cumple con el formato esperado.",
                        'ip' => $request->ip(),
                        'data' => $decoded ? json_encode($decoded) : '',
                        'terminal_id' => $terminal->id,
                        'sucursal_id' => $terminal->sucursal_id,
                        'cliente_id' => $terminal->sucursal->cliente_id,
                        'es_vk' => 1
                    ]);
                    DB::rollBack();
                    return response()->json(['success' => false, 'error' => "La propiedad 'name' no cumple con el formato esperado."], 400);
                }

                if (isset($item['seat']))
                    $itemItecketVK->asiento = $item['seat'];

                $itemItecketVK->save();

                if (isset($item['modifiers']) && count($item['modifiers']) > 0) {
                    $ids = [];
                    foreach ($item['modifiers'] as $modificador) {
                        if ($modificador) {
                            $modificadorDB = ModificadorVK::firstOrCreate([
                                'nombre' => $modificador
                            ]);
                            $ids[] = $modificadorDB->id;
                        }
                    }
                    $itemItecketVK->modificadores()->sync($ids);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            ModelsLog::create([
                'log' => "Error recibiendo ticket json. Error: {$e->getMessage()}",
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => "Error recibiendo ticket json. Error: {$e->getMessage()}",
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 1
            ]);
            Log::error("Error recibiendo ticket json. Error: {$e->getMessage()}");
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Error recibiendo ticket json'], 400);
        }

        return response()->json(['success' => true]);
    }
}
