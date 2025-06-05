<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\PersistRelations;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ImportData implements ToCollection, WithChunkReading
{
    public $result = [
        'codigos_obsoletos' => [],
        'diferencias' => [],
    ];
    public function collection(Collection $collection)
    {
        // TODO: Implement collection() method.
//        dd($collection);
        foreach ($collection as $row){
            $codigo = $row[0];
            $materialPeligroso = $row[3];

            $elemento = DB::table('tb_claves_prod_servs_cp')
            ->select('*')
            ->where('clave', $codigo)
            ->first();//ClaveProdServCp::where('clave', $codigo)->first();
            if(!$elemento){
                $this->result['codigos_obsoletos'][] = $codigo;
                Log::error("Codigo no encontrado: $codigo");
                continue;
            }
            if($elemento->es_material_peligroso != $materialPeligroso){
                $this->result['diferencias'][] = "$codigo - Valor actual: $elemento->es_material_peligroso - Valor correcto: $materialPeligroso";
                Log::error("Diferencias: $codigo - Valor actual: $elemento->es_material_peligroso - Valor correcto: $materialPeligroso");
            }
        }

        return $this->result;
    }

//    public function model(array $row)
//    {
//        // TODO: Implement model() method.
//        if($row[0]){
//            $codigo = $row[0];
//            $materialPeligroso = $row[3];
//
//            $element = ClaveProdServCp::where('clave', $codigo)->first();
//
//            if(!$element){
//                return null;
//            }
//            $element->es_material_peligroso = $materialPeligroso;
//            $element->save();
//            return $element;
//        }
//    }

    public function chunkSize(): int
    {
        // TODO: Implement chunkSize() method.
        return 100;
    }
}
