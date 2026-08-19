<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbTipoComprobantesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_tipo_comprobantes')->delete();

        $rows = [
            ['id' => 1, 'codigo' => 'I', 'descripcion' => 'Ingreso', 'activo' => 1, 'created_at' => '2020-06-18 00:34:03', 'updated_at' => '2020-06-18 01:34:03'],
            ['id' => 2, 'codigo' => 'E', 'descripcion' => 'Egreso', 'activo' => 1, 'created_at' => '2020-06-18 00:35:10', 'updated_at' => '2020-06-18 01:35:10'],
            ['id' => 3, 'codigo' => 'T', 'descripcion' => 'Traslado', 'activo' => 1, 'created_at' => '2020-06-18 01:35:35', 'updated_at' => '2020-06-18 01:35:35'],
            ['id' => 4, 'codigo' => 'N', 'descripcion' => 'Nómina', 'activo' => 1, 'created_at' => '2020-06-18 01:35:48', 'updated_at' => '2020-06-18 01:35:48'],
            ['id' => 5, 'codigo' => 'P', 'descripcion' => 'Pago', 'activo' => 1, 'created_at' => '2020-06-18 01:36:09', 'updated_at' => '2020-06-18 01:36:09'],
            ['id' => 6, 'codigo' => 'R', 'descripcion' => 'Remisión no deducible', 'activo' => 1, 'created_at' => '2024-12-06 17:53:43', 'updated_at' => '2024-12-06 17:54:14'],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_tipo_comprobantes')->insert($chunk);
        }
    }
}
