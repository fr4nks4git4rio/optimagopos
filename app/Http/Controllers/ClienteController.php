<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function loadClientes(Request $request)
    {
        $label = $request->label ?: 'nombre_comercial';

        if (user()->cliente_id)
            return response()->json(['success' => true, 'items' => []]);

        $query = DB::table('tb_clientes')
            ->where('deleted_at', null)
            ->where('es_cliente', 1)
            ->select('id', $label);

        $clientes = [];
        $query->get()->map(function ($cliente) use ($request, &$clientes, $label) {
            $cliente->text = Crypt::decrypt($cliente->{$label});
            if (str_contains(strtoupper($cliente->text), strtoupper($request->term))) {
                $clientes[] = $cliente;
            }
        });

        if ($request->filtro) {
            $clientes = Arr::prepend($clientes, ['id' => -1, 'text' => 'Todos']);
        }

        return response()->json(['success' => true, 'items' => $clientes]);
    }

    public function loadComensales(Request $request)
    {
        $label = $request->label ?: 'nombre_comercial';

        $cliente = Cliente::find(user()->cliente_id);
        $query = $cliente->comensalescomensales_activos()->newQuery();

        $query->select('id', $label);

        if ($request->term) {
            $clientes = [];
            $query->get()->map(function ($cliente) use ($request, &$clientes, $label) {
                $cliente->text = Crypt::decrypt($cliente->{$label});
                if (str_contains(strtoupper($cliente->text), strtoupper($request->term))) {
                    $clientes[] = $cliente;
                }
            });
        } else {
            $clientes = [];
        }

        if ($request->filtro) {
            $clientes = Arr::prepend($clientes, ['id' => -1, 'text' => 'Todos']);
        }

        return response()->json(['success' => true, 'items' => $clientes]);
    }
}
