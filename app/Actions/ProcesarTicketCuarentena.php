<?php

namespace App\Actions;

use App\Models\Cliente;
use App\Models\Cuarentena;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Terminal;
use App\Models\Ticket;
use App\Models\TicketProducto;
use App\Models\TicketProductoCorreccion;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ProcesarTicketCuarentena
{

    public Cuarentena $registro;

    public function __construct($registro)
    {
        $this->registro = $registro;
    }

    public function execute()
    {
        $decoded = json_decode($this->registro->data, true);
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
            $this->registro->update([
                'texto' => 'JSON inválido o incompleto'
            ]);
            return false;
        }

        $terminalId = $decoded['TerminalId'] ?? $decoded['MerchantFiscalId'];

        $terminal = Terminal::findByIdentificador($terminalId);

        if (!$terminal) {
            $terminalId = $decoded['APIUserName'];
            $terminal = Terminal::findByIdentificador($terminalId);
        }

        if (!$terminal) {
            $this->registro->update([
                'texto' => 'Terminal no encontrada.'
            ]);
            return false;
        }

        $terminal->id_pos = $decoded['PosId'];
        $terminal->save();

        DB::beginTransaction();

        try {
            // Paso 4: Acceder a datos generales
            if (!$decoded['ClerkId']) {
                $this->registro->update([
                    'texto' => 'Id de empleado no recibido.'
                ]);
                DB::rollBack();
                return false;
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

            if (Ticket::where('id_transaccion', $decoded['TransactionId'])->where('terminal_id', $terminal->id)->exists()) {
                $this->registro->update([
                    'texto' => "Ya existe un ticket para la terminal {$terminal->identificador} con el mismo id de transacción: {$decoded['TransactionId']}."
                ]);
                DB::rollBack();
                return false;
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
                        $this->registro->update([
                            'texto' => 'Propiedad no recibida en ítem Tax. Propiedades esperadas: Name y Amount.'
                        ]);
                        DB::rollBack();
                        return false;
                    }
                    $ticket->impuestos()->create([
                        'nombre' => $item['Name'],
                        'monto' => $item['Amount']
                    ]);
                }

                if ($type === 'Tender') {
                    if (!isset($item['Name']) || !isset($item['Amount'])) {
                        $this->registro->update([
                            'texto' => 'Propiedad no recibida en ítem Tender. Propiedades esperadas: Name y Amount.'
                        ]);
                        DB::rollBack();
                        return false;
                    }
                    $forma_pago = DB::table('tb_sucursal_forma_pagos')
                        ->where('sucursal_id', $terminal->sucursal_id)
                        ->where('nombre', $item['Name'])
                        ->whereNull('deleted_at')
                        ->get()->first();
                    if (!$forma_pago) {
                        $this->registro->update([
                            'texto' => 'Forma de pago no encontrada.'
                        ]);
                        DB::rollBack();
                        return false;
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
                    if (!isset($item['Id']) || !isset($item['Name']) || !isset($item['Amount']) || !isset($item['Qty']) || !$item['DepartmentId'] || !$item['DepartmentName']) {
                        $this->registro->update([
                            'texto' => 'Propiedad no recibida en ítem Product. Propiedades esperadas: Id, Name, Amount, Qty, DepartmentId y DepartmentName.'
                        ]);
                        DB::rollBack();
                        return false;
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
                    $discount = $item['Discount'] ? (float)$item['Discount'] : 0;
                    $ticketProducto = TicketProducto::where('ticket_id', $ticket->id)->where('producto_id', $producto->id)->where('departamento_id', $departamento->id)->first();
                    if (!$ticketProducto) {
                        $ticketProducto = new TicketProducto();
                        $ticketProducto->ticket_id = $ticket->id;
                        $ticketProducto->producto_id = $producto->id;
                        $ticketProducto->departamento_id = $departamento->id;
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
                        $this->registro->update([
                            'texto' => 'Propiedad no recibida en ítem Correction. Propiedades esperadas: Name, Amount y Qty.'
                        ]);
                        DB::rollBack();
                        return false;
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
            return true;
        } catch (Exception $e) {
            $this->registro->update([
                'texto' => "Error recibiendo ticket json. Error: {$e->getMessage()}"
            ]);
            DB::rollBack();
            return false;
        }
    }
}
