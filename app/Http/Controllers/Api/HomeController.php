<?php

namespace App\Http\Controllers\Api;

use App\Models\Administracion\CodificadoresGenerales\PuntoRuta;
use App\Models\API\CargaObject;
use App\Models\API\GlobalSiteValues;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Terminal;
use App\Models\Ticket;
use App\Models\TicketProductoCorreccion;
use App\Models\User;
use App\Models\Ventas\OrdenServicio;
use App\Models\Ventas\OrdenServicioEvidencia;
use App\Models\Ventas\OrdenServicioImagen;
use App\Notifications\SiteNotification;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HomeController
{
    public function parseJson(Request $request)
    {
        // Paso 1: Obtener contenido crudo
        $raw = $request->getContent();

        Log::error($raw);

        // Paso 2: El contenido viene entre comillas dobles con JSON escapado
        $decoded = json_decode(trim($raw, '"'), true); // quitar comillas exteriores

        // Paso 3: Verificar si se decodificó correctamente
        if (!$decoded || !isset($decoded['Items'])) {
            return response()->json(['error' => 'JSON inválido o incompleto'], 400);
        }

        $terminal = Terminal::findByIdentificador($decoded['Company']);

        if (!$terminal) {
            return response()->json(['error' => 'Terminal no encontrada'], 400);
        }
        // Paso 4: Acceder a datos generales
        $clerk = Empleado::where('sucursal_id', $terminal->sucursal_id)->where('id_empleado', $decoded['ClerkId'])->first();
        if (!$clerk) {
            $clerk = Empleado::create([
                'id_empleado' => $decoded['ClerkId'],
                'nombre' => $decoded['ClerkName'],
                'sucursal_id' => $terminal->sucursal_id
            ]);
        }

        $items = $decoded['Items'] ?? [];

        $ticket = Ticket::create([
            'ubicacion' => $decoded['Location'] ?? '',
            'id_transaccion' => $decoded['TransactionId'],
            'fecha_transaccion' => $decoded['TransactionStartTime'] ?? null,
            'empleado_id' => $clerk->id,
            'sucursal_id' => $terminal->sucursal_id,
            'terminal_id' => $terminal->id
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
                // Guardar método de pago en tabla pagos
                $ticket->operaciones()->create([
                    'nombre' => $item['Name'] ?? '',
                    'monto' => $item['Amount'] ?? 0
                ]);
                if ($item['Tip'] != '') {
                    $ticket->propinas()->create([
                        'monto' => (float)$item['Tip'],
                        'empleado_id' => $clerk->id
                    ]);
                }
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
                    ->where('id_departamento', $item['Department_Id'])
                    ->first();
                if (!$departamento) {
                    $departamento = Departamento::create([
                        'id_departamento' => $item['Department_Id'],
                        'nombre' => $item['DepartmentName'],
                        'sucursal_id' => $terminal->sucursal_id
                    ]);
                }

                if ($correccion) {
                    $prod = $ticket->productos()->where('producto_id', $producto->id)->where('departamento_id', $departamento->id)->first();
                    $correccion->producto_id = optional($prod)->id;
                    $correccion->cantidad = abs($item['Qty']);
                    $correccion->precio = abs($item['Amount']);
                    $correccion->save();
                    $correccion = null;
                    continue;
                } else {
                    $ticketProducto = $ticket->productos()->create([
                        'precio' => $item['Amount'] ?? 0,
                        'cantidad' => $item['Qty'] ?? 0,
                        'descuento' => $item['Discount'] ?? 0,
                    ]);
                    $importe += $ticketProducto->precio - $ticketProducto->descuento;
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
