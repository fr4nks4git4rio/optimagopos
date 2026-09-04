<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CfdiController extends Controller
{
    public function loadCfdis(Request $request)
    {
        $query = DB::table('tb_cfdis')
            ->where('activo', 1)
            ->select('id', 'codigo', 'descripcion', DB::raw('CONCAT(descripcion, " (", codigo, ")") as text'));

        if($request->term){
            $cfdis = $query->whereRaw('CONCAT(descripcion, " (", codigo, ")") like ?',['%'.$request->term.'%'])->limit(50)->get()->toArray();
        }else{
            $cfdis = [];
        }

        return response()->json(['success' => true, 'items' => $cfdis]);

    }
}
