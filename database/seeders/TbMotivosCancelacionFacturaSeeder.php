<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbMotivosCancelacionFacturaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_motivos_cancelacion_factura')->delete();

        $rows = [
            ['id' => 1, 'codigo' => '01', 'descripcion' => 'Comprobante emitido con errores con relación', 'created_at' => '2022-05-15 17:00:04', 'updated_at' => null],
            ['id' => 2, 'codigo' => '02', 'descripcion' => 'Comprobante emitido con errores sin relación', 'created_at' => '2022-05-15 17:00:40', 'updated_at' => null],
            ['id' => 3, 'codigo' => '03', 'descripcion' => 'No se llevó a cabo la operación', 'created_at' => '2022-05-15 17:00:42', 'updated_at' => null],
            ['id' => 4, 'codigo' => '04', 'descripcion' => 'Operación nominativa relacionada en la factura global', 'created_at' => '2022-05-15 17:00:43', 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_motivos_cancelacion_factura')->insert($chunk);
        }
    }
}
