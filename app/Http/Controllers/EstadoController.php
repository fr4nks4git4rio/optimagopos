<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadoController extends Controller
{
    public function loadEstados(Request $request)
    {
        $query = DB::table('tb_estados')
            ->where('activo', 1)
            ->select('id', DB::raw("concat(codigo, ' - ', nombre) as text"));

        $estados = [];
        if ($request->term != '') {
            $estados = $query->whereRaw("concat(codigo, ' - ', nombre) like ?", ['%' . $request->term . '%'])
                ->get()->toArray();
        }

        return response()->json(['success' => true, 'items' => $estados]);

    }
}
