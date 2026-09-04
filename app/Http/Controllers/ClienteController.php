<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function loadClientes(Request $request)
    {
        $label = $request->label ?: 'nombre_comercial';

        if (!user()->hasAnyRole(['SuperAdmin', 'Accountant']))
            return response()->json(['success' => true, 'items' => []]);

        $query = DB::table('tb_clientes')
            ->where('deleted_at', null)
            ->where('es_cliente', 1)
            ->select('id', $label);

        // Cache 5 min (single-server): evita re-desencriptar toda la tabla en cada
        // tecla del autocomplete. El cifrado se mantiene intacto.
        $base = Cache::remember(
            'busc|clientes|' . $label,
            now()->addMinutes(5),
            fn() => $query->get()->map(fn($c) => ['id' => $c->id, 'text' => Crypt::decrypt($c->{$label})])->toArray()
        );

        $clientes = [];
        $term = strtoupper($request->term ?? '');
        foreach ($base as $cliente) {
            if ($term === '' || str_contains(strtoupper($cliente['text']), $term)) {
                $clientes[] = $cliente;
                if (count($clientes) >= 50) {
                    break;
                }
            }
        }

        if ($request->filtro) {
            $clientes = Arr::prepend($clientes, ['id' => -1, 'text' => 'Todos']);
        }

        return response()->json(['success' => true, 'items' => $clientes]);
    }

    public function loadComensales(Request $request)
    {
        $label = $request->label ?: 'nombre_comercial';

        $cliente = Cliente::find(user()->cliente_id);
        $query = $cliente->comensales_activos()->newQuery();

        $query->select('id', $label);

        if ($request->term) {
            // Cache 5 min (single-server): evita re-desencriptar en cada tecla.
            $base = Cache::remember(
                'busc|comensales|' . user()->cliente_id . '|' . $label,
                now()->addMinutes(5),
                function () use ($query, $label) {
                    return $query->get()->map(fn($c) => ['id' => $c->id, 'text' => Crypt::decrypt($c->{$label})])->toArray();
                }
            );
            $clientes = [];
            $term = strtoupper($request->term);
            foreach ($base as $cliente) {
                if (str_contains(strtoupper($cliente['text']), $term)) {
                    $clientes[] = $cliente;
                    if (count($clientes) >= 50) {
                        break;
                    }
                }
            }
        } else {
            $clientes = [];
        }

        if ($request->filtro) {
            $clientes = Arr::prepend($clientes, ['id' => -1, 'text' => 'Todos']);
        }

        return response()->json(['success' => true, 'items' => $clientes]);
    }
}
