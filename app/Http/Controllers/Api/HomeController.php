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
use App\Models\UbicacionVk;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HomeController
{
    public function parseTicketJson(Request $request)
    {
        // return response()->json(['success' => false, 'message' => 'API fuera de servicio.']);
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
                'log' => __('site.data_parser.incomplete_jason'),
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 400
            ]);

            Cuarentena::create([
                'texto' => __('site.data_parser.incomplete_jason'),
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 0
            ]);
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
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
                'log' => __('site.data_parser.terminal_not_found'),
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => __('site.data_parser.terminal_not_found'),
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 0
            ]);
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
        }

        if ($terminal->es_vk) {
            ModelsLog::create([
                'log' => __('site.data_parser.terminal_is_vk'),
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => __('site.data_parser.terminal_is_vk'),
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'cliente_id' => $terminal->sucursal->cliente_id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'es_vk' => 0
            ]);
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
        }

        $terminal->id_pos = $decoded['PosId'];
        $terminal->save();

        DB::beginTransaction();

        try {
            // Paso 4: Acceder a datos generales
            if (!$decoded['ClerkId']) {
                ModelsLog::create([
                    'log' => __('site.data_parser.employee_id_not_received'),
                    'data' => $decoded ? json_encode($decoded) : '',
                    'status' => 400,
                    'sucursal_id' => $terminal->sucursal_id
                ]);
                Cuarentena::create([
                    'texto' => __('site.data_parser.employee_id_not_received'),
                    'ip' => $request->ip(),
                    'data' => $decoded ? json_encode($decoded) : '',
                    'cliente_id' => $terminal->sucursal->cliente_id,
                    'sucursal_id' => $terminal->sucursal_id,
                    'terminal_id' => $terminal->id,
                    'es_vk' => 0
                ]);
                return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
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

            if (Ticket::where('id_transaccion', $decoded['TransactionId'])->where('terminal_id', $terminal->id)->where('fecha_transaccion', Carbon::createFromFormat('d/m/Y H:i:s', $decoded['TransactionStartTime'])->format('Y-m-d H:i:s'))->exists()) {
                ModelsLog::create([
                    'log' => __('site.data_parser.id_transaction_already_exists', ['terminal' => $terminal->identificador, 'id_transaction' => $decoded['TransactionId']]),
                    'data' => $decoded ? json_encode($decoded) : '',
                    'status' => 400,
                    'sucursal_id' => $terminal->sucursal_id
                ]);
                Cuarentena::create([
                    'texto' => __('site.data_parser.id_transaction_already_exists', ['terminal' => $terminal->identificador, 'id_transaction' => $decoded['TransactionId']]),
                    'ip' => $request->ip(),
                    'data' => $decoded ? json_encode($decoded) : '',
                    'cliente_id' => $terminal->sucursal->cliente_id,
                    'sucursal_id' => $terminal->sucursal_id,
                    'terminal_id' => $terminal->id,
                    'es_vk' => 0
                ]);
                return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
            }

            $ticket = Ticket::create([
                'ubicacion' => $decoded['Location'] ?? '',
                'id_transaccion' => $decoded['TransactionId'],
                'fecha_transaccion' => Carbon::createFromFormat('d/m/Y H:i:s', $decoded['TransactionStartTime'])->format('Y-m-d H:i:s'),
                'vigencia_facturacion' => $vigencia_facturacion ? $vigencia_facturacion->format('Y-m-d') : null,
                'modo_entrenamiento' => isset($decoded['TrainingMode']) && $decoded['TrainingMode'],
                'empleado_id' => optional($clerk)->id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'comensal_id' => $comensal ? $comensal->id : null
            ]);

            // Ejemplo de lógica condicional por tipo de ítem
            $correccion = null;
            $importe = 0;
            $prevProduct = null;
            $tenders = [];
            $poras = [];
            foreach ($items as $pos => $item) {
                $type = $item['Type'] ?? 'Product';

                if ($type === 'PORA') {
                    $item['pos'] = $pos;
                    $poras[] = $item;
                }
                if ($type === 'Tax') {
                    if (!isset($item['Name']) || !isset($item['Amount']) || !isset($item['Taxable'])) {
                        DB::rollBack();
                        ModelsLog::create([
                            'log' => __('site.data_parser.properties_not_found', ['item' => 'Tax', 'properties' => 'Name, Amount ' . __('site.common.and') . ' Taxable']),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => __('site.data_parser.properties_not_found', ['item' => 'Tax', 'properties' => 'Name, Amount ' . __('site.common.and') . ' Taxable']),
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
                    }
                    $ticket->impuestos()->create([
                        'nombre' => $item['Name'],
                        'monto' => $item['Amount'],
                        'gravable' => $item['Taxable']
                    ]);
                }

                if ($type === 'Tender') {
                    if (!isset($item['Name']) || !isset($item['Amount'])) {
                        DB::rollBack();
                        ModelsLog::create([
                            'log' => __('site.data_parser.properties_not_found', ['item' => 'Tender', 'properties' => 'Name ' . __('site.common.and') . ' Amount']),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => __('site.data_parser.properties_not_found', ['item' => 'Tender', 'properties' => 'Name ' . __('site.common.and') . ' Amount']),
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
                    }
                    $forma_pago = DB::table('tb_sucursal_forma_pagos')
                        ->where('sucursal_id', $terminal->sucursal_id)
                        ->where('nombre', $item['Name'])
                        ->whereNull('deleted_at')
                        ->get()->first();
                    if (!$forma_pago) {
                        DB::rollBack();
                        ModelsLog::create([
                            'log' => __('site.data_parser.payment_form_not_found', ['payment_form' => $item['Name']]),
                            'data' => json_encode($decoded),
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);

                        Cuarentena::create([
                            'texto' => __('site.data_parser.payment_form_not_found', ['payment_form' => $item['Name']]),
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
                    }
                    $tasa_cambio = 1;
                    if ($forma_pago && $forma_pago->moneda_id != $terminal->sucursal->moneda_base_id) {
                        $tipo_cambio = get_tipo_cambio($forma_pago->moneda_id, $terminal->sucursal->moneda_base_id, $terminal->sucursal->id);
                        if ($tipo_cambio->id) {
                            $tasa_cambio = $tipo_cambio->tasa;
                        }
                    }
                    $item['pos'] = $pos;
                    $tenders[] = $item;
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
                        DB::rollBack();
                        ModelsLog::create([
                            'log' => __('site.data_parser.properties_not_found', ['item' => 'Product', 'properties' => 'Id, Name, Amount, Qty, DepartmentId ' . __('site.common.and') . ' DepartmentName']),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => __('site.data_parser.properties_not_found', ['item' => 'Product', 'properties' => 'Id, Name, Amount, Qty, DepartmentId ' . __('site.common.and') . ' DepartmentName']),
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
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

                if ($type === 'Department') {
                    if (!isset($item['Id']) || !isset($item['Name']) || !isset($item['Amount']) || !isset($item['Qty'])) {
                        DB::rollBack();
                        ModelsLog::create([
                            'log' => __('site.data_parser.properties_not_found', ['item' => 'Department', 'properties' => 'Id, Name, Amount ' . __('site.common.and') . ' Qty']),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => __('site.data_parser.properties_not_found', ['item' => 'Department', 'properties' => 'Id, Name, Amount ' . __('site.common.and') . ' Qty']),
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
                    }

                    $departamento = Departamento::where('sucursal_id', $terminal->sucursal_id)
                        ->where('id_departamento', $item['Id'])
                        ->first();
                    if (!$departamento) {
                        $departamento = Departamento::create([
                            'id_departamento' => $item['Id'],
                            'nombre' => $item['Name'],
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                    }

                    $qty = $item['Qty'] ? (float)$item['Qty'] : 0;
                    $amount = $item['Amount'] ? (float)$item['Amount'] : 0;
                    $discount = $item['Discount'] ? (float)$item['Discount'] : 0;
                    $ticketDepartamento = TicketProducto::where('ticket_id', $ticket->id)->where('departamento_id', $departamento->id)->whereNull('producto_id')->first();
                    if (!$ticketDepartamento) {
                        $ticketDepartamento = new TicketProducto();
                        $ticketDepartamento->ticket_id = $ticket->id;
                        $ticketDepartamento->departamento_id = $departamento->id;
                        $ticketDepartamento->precio = 0;
                        $ticketDepartamento->cantidad = 0;
                        $ticketDepartamento->descuento = 0;
                    }
                    $ticketDepartamento->precio += $amount;
                    $ticketDepartamento->cantidad += $qty;
                    $ticketDepartamento->descuento += $discount;
                    $ticketDepartamento->save();

                    $importe += $amount - $discount;
                }

                if ($type === 'Correction') {
                    if (!isset($item['Name']) || !isset($item['Amount']) || !isset($item['Qty'])) {
                        DB::rollBack();
                        ModelsLog::create([
                            'log' => __('site.data_parser.properties_not_found', ['item' => 'Correction', 'properties' => 'Name, Amount ' . __('site.common.and') . ' Qty']),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'status' => 400,
                            'sucursal_id' => $terminal->sucursal_id
                        ]);
                        Cuarentena::create([
                            'texto' => __('site.data_parser.properties_not_found', ['item' => 'Correction', 'properties' => 'Name, Amount ' . __('site.common.and') . ' Qty']),
                            'ip' => $request->ip(),
                            'data' => $decoded ? json_encode($decoded) : '',
                            'terminal_id' => $terminal->id,
                            'sucursal_id' => $terminal->sucursal_id,
                            'cliente_id' => $terminal->sucursal->cliente_id,
                            'es_vk' => 0
                        ]);
                        return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
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

            if (count($poras) > 0) {
                foreach ($poras as $pora) {
                    $ts = array_values(array_filter($tenders, function ($value) use ($pora) {
                        return $value['Amount'] == $pora['Amount'];
                    }));

                    if (count($ts) == 0)
                        continue;

                    if (count($ts) == 1) {
                        $ts = $ts[0];
                        $forma_pago = DB::table('tb_sucursal_forma_pagos')
                            ->where('sucursal_id', $terminal->sucursal_id)
                            ->where('nombre', $ts['Name'])
                            ->whereNull('deleted_at')
                            ->get()->first();
                        $ticket->operaciones()->create([
                            'nombre' => $ts['Name'] ?? '',
                            'monto' => $pora['Amount'] ?? 0,
                            'sucursal_forma_pago_id' => $forma_pago->id,
                            'es_pora' => 1,
                            'nombre_pora' => $pora['Name']
                        ]);
                        foreach ($tenders as $i => $t)
                            if ($t['pos']  == $ts['pos']) {
                                array_splice($tenders, $i, 1);
                                $tenders = array_values($tenders);
                                break;
                            }

                        continue;
                    }

                    $referencia = $pora['pos'];
                    $posiciones = [];
                    foreach ($ts as $t)
                        $posiciones[] = $t['pos'];
                    usort($posiciones, fn($a, $b) => abs($a - $referencia) <=> abs($b - $referencia));
                    foreach ($tenders as $i => $t) {
                        if ($t['pos'] == $posiciones[0]) {
                            $forma_pago = DB::table('tb_sucursal_forma_pagos')
                                ->where('sucursal_id', $terminal->sucursal_id)
                                ->where('nombre', $t['Name'])
                                ->whereNull('deleted_at')
                                ->get()->first();
                            $ticket->operaciones()->create([
                                'nombre' => $t['Name'] ?? '',
                                'monto' => $pora['Amount'] ?? 0,
                                'sucursal_forma_pago_id' => $forma_pago->id,
                                'es_pora' => 1,
                                'nombre_pora' => $pora['Name']
                            ]);
                            array_splice($tenders, $i, 1);
                            $tenders = array_values($tenders);
                            break;
                        }
                    }
                }
            }

            $ticket->importe = $importe;
            $ticket->save();

            ModelsLog::create([
                'log' => __('site.data_parser.data_received'),
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 200,
                'sucursal_id' => $terminal->sucursal_id
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            ModelsLog::create([
                'log' => __('site.data_parser.exception_error', ['error' => $e->getMessage()]),
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => __('site.data_parser.exception_error', ['error' => $e->getMessage() . ' ' . $e->getTraceAsString()]),
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 0
            ]);
            Log::error(__('site.data_parser.exception_error', ['error' => $e->getMessage() . ' ' . $e->getTraceAsString()]));
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
        }

        return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
    }

    public function parseTicketVKJson(Request $request)
    {
        // return response()->json(['success' => false, 'message' => 'API fuera de servicio.']);
        $raw = $request->getContent();

        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        if (
            !$decoded
            || !isset($decoded['TerminalId'])
            || !isset($decoded['Data'])
            || !isset($decoded['Data']['items'])
            || !isset($decoded['Data']['OrderStatus'])
            || !isset($decoded['Data']['orderNumber'])
            || !isset($decoded['Data']['timestamp'])
        ) {
            ModelsLog::create([
                'log' => __('site.data_parser.incomplete_jason'),
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 400
            ]);

            Cuarentena::create([
                'texto' => __('site.data_parser.incomplete_jason'),
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 1
            ]);
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
        }

        $terminalId = $decoded['TerminalId'];

        $terminal = Terminal::findByIdentificador($terminalId);

        if (!$terminal) {
            ModelsLog::create([
                'log' => __('site.data_parser.terminal_not_found'),
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => __('site.data_parser.terminal_not_found'),
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 1
            ]);
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
        }

        if (!$terminal->es_vk) {
            ModelsLog::create([
                'log' => __('site.data_parser.terminal_not_vk'),
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => __('site.data_parser.terminal_not_vk'),
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'cliente_id' => $terminal->sucursal->cliente_id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'es_vk' => 1
            ]);
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
        }

        $ticket_vk = TicketVK::where('terminal_id', $terminal->id)->where('id_transaccion', $decoded['Data']['orderNumber'])->first();
        if ($ticket_vk) {
            $update = [
                'estado' => $decoded['Data']['OrderStatus']
            ];
            switch ($decoded['Data']['OrderStatus']) {
                case 2:
                    $update['fecha_en_proceso'] = parse_fecha_espanol($decoded['Data']['timestamp'])->format('Y-m-d H:i:s');
                    break;
                case 3:
                    $update['fecha_terminado'] = parse_fecha_espanol($decoded['Data']['timestamp'])->format('Y-m-d H:i:s');
                    break;
                case 4:
                    $update['fecha_demorado'] = parse_fecha_espanol($decoded['Data']['timestamp'])->format('Y-m-d H:i:s');
                    break;
            }
            $ticket_vk->update($update);
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
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

            $ubicacion = null;
            if ($data['LocationId']) {
                $ubicacion = UbicacionVk::where('sucursal_id', $terminal->sucursal_id)
                    ->where('id_ubicacion', $data['LocationId'])
                    ->first();
                if (!$ubicacion) {
                    $ubicacion = UbicacionVk::create([
                        'id_ubicacion' => $data['LocationId'],
                        'nombre' => $data['location'],
                        'sucursal_id' => $terminal->sucursal_id
                    ]);
                }
            }

            $ticketVK = TicketVK::create([
                'mesa' => isset($data['table']) && $data['table'] ? $data['table'] : '',
                'asiento' => isset($data['seat']) && $data['seat'] ? $data['seat'] : '',
                'fecha_transaccion' => parse_fecha_espanol($data['timestamp'])->format('Y-m-d H:i:s'),
                'estado' => $data['OrderStatus'],
                'id_transaccion' => $data['orderNumber'],
                'pos_ip' => $data['PosIpAddress'],
                'tiempo_resolver' => $data['TimeToResolve'],
                'porciento_alerta_estado' => $data['WarningStatusThresholdInPercent'],
                'empleado_id' => $empleado?->id,
                'sucursal_id' => $terminal->sucursal_id,
                'terminal_id' => $terminal->id,
                'ubicacion_id' => $ubicacion?->id,
            ]);

            $items = $data['items'] ?? [];
            foreach ($items as $item) {
                $itemItecketVK = new ItemTicketVK();
                $itemItecketVK->ticket_vk_id = $ticketVK->id;
                if (!isset($item['name'])) {
                    DB::rollBack();
                    ModelsLog::create([
                        'log' => __('site.data_parser.property_not_found_in_package', ['property' => 'name']),
                        'data' => json_encode($decoded),
                        'status' => 400,
                        'sucursal_id' => $terminal->sucursal_id
                    ]);

                    Cuarentena::create([
                        'texto' => __('site.data_parser.property_not_found_in_package', ['property' => 'name']),
                        'ip' => $request->ip(),
                        'data' => $decoded ? json_encode($decoded) : '',
                        'terminal_id' => $terminal->id,
                        'sucursal_id' => $terminal->sucursal_id,
                        'cliente_id' => $terminal->sucursal->cliente_id,
                        'es_vk' => 1
                    ]);
                    return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
                }

                $regex = '/^\s*(\d+(?:[.,]\d+)?)\s+(.+?)\s*$/';
                if (preg_match($regex, $item['name'], $coincidencias)) {
                    $itemItecketVK->cantidad = $coincidencias[1];
                    $itemItecketVK->nombre = $coincidencias[2];
                } else {
                    DB::rollBack();
                    ModelsLog::create([
                        'log' => __('site.data_parser.property_invalid_format', ['property' => 'name']),
                        'data' => json_encode($decoded),
                        'status' => 400,
                        'sucursal_id' => $terminal->sucursal_id
                    ]);

                    Cuarentena::create([
                        'texto' => __('site.data_parser.property_invalid_format', ['property' => 'name']),
                        'ip' => $request->ip(),
                        'data' => $decoded ? json_encode($decoded) : '',
                        'terminal_id' => $terminal->id,
                        'sucursal_id' => $terminal->sucursal_id,
                        'cliente_id' => $terminal->sucursal->cliente_id,
                        'es_vk' => 1
                    ]);
                    return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
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

            ModelsLog::create([
                'log' => __('site.data_parser.data_received'),
                'data' => $decoded ? json_encode($decoded) : '',
                'status' => 200,
                'sucursal_id' => $terminal->sucursal_id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            ModelsLog::create([
                'log' => __('site.data_parser.exception_error', ['error' => $e->getMessage()]),
                'data' => json_encode($decoded),
                'status' => 400
            ]);
            Cuarentena::create([
                'texto' => __('site.data_parser.exception_error', ['error' => $e->getMessage().' '.$e->getTraceAsString()]),
                'ip' => $request->ip(),
                'data' => $decoded ? json_encode($decoded) : '',
                'es_vk' => 1
            ]);
            Log::error(__('site.data_parser.exception_error', ['error' => $e->getMessage().' '.$e->getTraceAsString()]));
            return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
        }

        return response()->json(['success' => true, 'message' => __('site.data_parser.data_received')]);
    }
}
