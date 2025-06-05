<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SerieController extends Controller
{
    public function loadSeries(Request $request)
    {
        $query = DB::table('tb_series')
            ->where('activo', 1)
            ->select('id', 'descripcion as text');

        if($request->term){
            $series = $query->where('descripcion', 'like', '%'.$request->term.'%')->get()->toArray();
        }else{
            $series = [];
        }

        return response()->json(['success' => true, 'items' => $series]);

    }
}
