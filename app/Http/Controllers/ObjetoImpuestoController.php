<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjetoImpuestoController extends Controller
{
    public function loadObjetosImpuestos(Request $request)
    {
        $query = DB::table('tb_objetos_impuesto')
            ->where('activo', 1)
            ->select('id', 'clave', 'descripcion', DB::raw('CONCAT(descripcion, " (", clave, ")") as text'));

        if($request->term){
            $objetosImpuestos = $query->whereRaw('CONCAT(descripcion, " (", clave, ")") like ?',['%'.$request->term.'%'])->get()->toArray();
        }else{
            $objetosImpuestos = [];
        }

        return response()->json(['success' => true, 'items' => $objetosImpuestos]);

    }
}
