<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoComprobanteController extends Controller
{
    public function loadTiposComprobantes(Request $request)
    {
        $query = DB::table('tb_tipo_comprobantes')
            ->where('activo', 1)
            ->select('id', 'codigo', 'descripcion', DB::raw('CONCAT(descripcion, " (", codigo, ")") as text'));

        if($request->term){
            $tiposComprobantes = $query->whereRaw('CONCAT(descripcion, " (", codigo, ")") like ?',['%'.$request->term.'%'])->get()->toArray();
        }else{
            $tiposComprobantes = [];
        }

        return response()->json(['success' => true, 'items' => $tiposComprobantes]);

    }
}
