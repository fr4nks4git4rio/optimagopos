<?php

namespace App\Actions;

use App\Models\Cliente;
use App\Models\Cuarentena;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\ItemTicketVK;
use App\Models\ModificadorVK;
use App\Models\Producto;
use App\Models\Terminal;
use App\Models\Ticket;
use App\Models\TicketProducto;
use App\Models\TicketProductoCorreccion;
use App\Models\TicketVK;
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
                'texto' => 'Terminal no encontrada'
            ]);
            return false;
        }

        if (!$terminal->es_vk) {
            $this->registro->update([
                'texto' => 'La Ternminal no está marcada como dispositivo de Video Kitchen'
            ]);
            return false;
        }

        if ($terminal->suscripcion->estado != 'ACTIVA') {
            $this->registro->update([
                'texto' => 'La Terminal no pertenece a una Suscripción ACTIVA.'
            ]);
            return false;
        }

        $ticket_vk = TicketVK::where('terminal_id', $terminal->id)->where('id_transaccion', $decoded['Data']['orderNumber'])->first();
        if ($ticket_vk) {
            $ticket_vk->update([
                'estado' => $decoded['Data']['OrderStatus']
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
                    $this->registro->update([
                        'texto' => 'Propiedad no recibida en item. Propiedad esperada: name'
                    ]);
                    DB::rollBack();
                    return false;
                }

                $regex = '/^\s*(\d+(?:[.,]\d+)?)\s+(.+?)\s*$/';
                if (preg_match($regex, $item['name'], $coincidencias)) {
                    $itemItecketVK->cantidad = $coincidencias[1];
                    $itemItecketVK->nombre = $coincidencias[2];
                } else {
                    $this->registro->update([
                        'texto' => "La propiedad 'name' no cumple con e formato esperado."
                    ]);
                    DB::rollBack();
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
            $this->registro->update([
                'texto' => "Error procesando el ticket json. Error: {$e->getMessage()}"
            ]);
            DB::rollBack();
            return false;
        }

        return true;
    }
}
