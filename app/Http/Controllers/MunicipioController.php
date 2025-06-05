<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MunicipioController extends Controller
{
    public function loadMunicipios(Request $request)
    {
        $query = DB::table('tb_municipios')
            ->where('activo', 1)
            ->select('id', DB::raw("concat(codigo, ' - ', nombre) as text"));

        $estado = isset($request->input()['estado_id']) ? $request->input()['estado_id'] : null;
        $municipios = [];
        if ($request->term != '') {
            $municipios = $query->whereRaw("concat(codigo, ' - ', nombre) like ?", ['%' . $request->term . '%'])
                ->where('estado_id', $estado)
                ->get()->toArray();
        }

        return response()->json(['success' => true, 'items' => $municipios]);

    }
}
