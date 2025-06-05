<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalidadController extends Controller
{
    public function loadLocalidades(Request $request)
    {
        $query = DB::table('tb_localidades')
            ->where('activo', 1)
            ->select('id', DB::raw("concat(codigo, ' - ', nombre) as text"));

        $estado = isset($request->input()['estado_id']) ? $request->input()['estado_id'] : null;
        $localidades = [];
        if ($request->term != '') {
            $localidades = $query->whereRaw("concat(codigo, ' - ', nombre) like ?", ['%' . $request->term . '%'])
                ->where('estado_id', $estado)
                ->get()->toArray();
        }

        return response()->json(['success' => true, 'items' => $localidades]);

    }
}
