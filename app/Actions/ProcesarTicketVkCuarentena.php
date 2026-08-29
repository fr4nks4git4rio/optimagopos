<?php

namespace App\Actions;

use App\Models\Cliente;
use App\Models\Cuarentena;
use App\Models\Empleado;
use App\Models\ItemTicketVK;
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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ProcesarTicketVkCuarentena
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
            || !isset($decoded['TerminalId'])
            || !isset($decoded['Data'])
            || !isset($decoded['Data']['items'])
            || !isset($decoded['Data']['OrderStatus'])
            || !isset($decoded['Data']['orderNumber'])
            || !isset($decoded['Data']['timestamp'])
        ) {
            $this->registro->update([
                'texto' => 'JSON inválido o incompleto'
            ]);
            return false;
        }

        $terminalId = $decoded['TerminalId'];

        $terminal = Terminal::findByIdentificador($terminalId);

        if (!$terminal) {
            $this->registro->update([
                'texto' => __('site.data_parser.terminal_not_found')
            ]);
            return false;
        }

        if (!$terminal->es_vk) {
            $this->registro->update([
                'texto' => __('site.data_parser.terminal_not_vk')
            ]);
            return false;
        }

        $ticket_vk = TicketVK::where('terminal_id', $terminal->id)->where('id_transaccion', $decoded['Data']['orderNumber'])->first();
        if ($ticket_vk) {
            $ticket_vk->update([
                'estado' => $decoded['Data']['OrderStatus'],
                'fecha_transaccion' => parse_fecha_espanol($decoded['Data']['timestamp'])->format('Y-m-d H:i:s')
            ]);
            return true;
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
                    $this->registro->update([
                        'texto' => __('site.data_parser.property_not_found_in_package', ['property' => 'name'])
                    ]);
                    return false;
                }

                $regex = '/^\s*(\d+(?:[.,]\d+)?)\s+(.+?)\s*$/';
                if (preg_match($regex, $item['name'], $coincidencias)) {
                    $itemItecketVK->cantidad = $coincidencias[1];
                    $itemItecketVK->nombre = $coincidencias[2];
                } else {
                    DB::rollBack();
                    $this->registro->update([
                        'texto' => __('site.data_parser.property_invalid_format', ['property' => 'name'])
                    ]);
                    return false;
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
            DB::rollBack();
            $this->registro->update([
                'texto' => __('site.data_parser.exception_error', ['error' => $e->getMessage().' '.$e->getTraceAsString()])
            ]);
            return false;
        }

        return true;
    }
}
