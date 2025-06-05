<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormaPagoController extends Controller
{
    public function loadFormasPagos(Request $request)
    {
        $query = DB::table('tb_forma_pagos')
            ->where('activo', 1)
            ->select('id', 'codigo', 'descripcion', DB::raw('CONCAT(descripcion, " (", codigo, ")") as text'));

        if($request->term){
            $formasPago = $query->whereRaw('CONCAT(descripcion, " (", codigo, ")") like ?',['%'.$request->term.'%'])->get()->toArray();
        }else{
            $formasPago = [];
        }

        return response()->json(['success' => true, 'items' => $formasPago]);

    }
}
