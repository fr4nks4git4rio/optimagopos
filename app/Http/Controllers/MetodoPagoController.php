<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetodoPagoController extends Controller
{
    public function loadMetodosPagos(Request $request)
    {
        $query = DB::table('tb_metodo_pagos')
            ->where('activo', 1)
            ->select('id', 'codigo', 'descripcion', DB::raw('CONCAT(descripcion, " (", codigo, ")") as text'));

        if($request->term){
            $metodosPago = $query->whereRaw('CONCAT(descripcion, " (", codigo, ")") like ?',['%'.$request->term.'%'])->get()->toArray();
        }else{
            $metodosPago = [];
        }

        return response()->json(['success' => true, 'items' => $metodosPago]);

    }
}
