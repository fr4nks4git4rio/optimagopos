<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaveProdServController extends Controller
{
    public function loadClavesProdServs(Request $request)
    {
        $query = DB::table('tb_clave_prod_servs')
            ->where('activo', 1)
            ->select('id', 'codigo', 'nombre', DB::raw('CONCAT(nombre, " (", codigo, ")") as text'));

        if($request->term){
            $clavesProdServs = $query->whereRaw('CONCAT(nombre, " (", codigo, ")") like ?',['%'.$request->term.'%'])->get()->toArray();
        }else{
            $clavesProdServs = [];
        }

        return response()->json(['success' => true, 'items' => $clavesProdServs]);

    }
}
