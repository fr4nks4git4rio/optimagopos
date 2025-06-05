<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaveUnidadController extends Controller
{
    public function loadClavesUnidades(Request $request)
    {
        $query = DB::table('tb_clave_unidades')
            ->where('activo', 1)
            ->select('id', 'codigo', 'descripcion', DB::raw('CONCAT(descripcion, " (", codigo, ")") as text'));

        if($request->term){
            $clavesUnidades = $query->whereRaw('CONCAT(descripcion, " (", codigo, ")") like ?',['%'.$request->term.'%'])->get()->toArray();
        }else{
            $clavesUnidades = [];
        }

        return response()->json(['success' => true, 'items' => $clavesUnidades]);

    }
}
