<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbClaveUnidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_clave_unidades')->delete();

        $rows = [
            ['id' => 1, 'codigo' => 'E48', 'descripcion' => 'Unidad de servicio', 'activo' => 1, 'created_at' => '2021-02-11 21:58:33', 'updated_at' => '2021-02-11 21:58:33'],
            ['id' => 2, 'codigo' => 'H87', 'descripcion' => 'Pieza', 'activo' => 1, 'created_at' => '2021-02-11 21:58:57', 'updated_at' => '2021-02-11 21:58:57'],
            ['id' => 3, 'codigo' => 'ACT', 'descripcion' => 'Unidades de Venta', 'activo' => 1, 'created_at' => '2022-10-05 17:46:38', 'updated_at' => '2024-12-06 17:57:51'],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_clave_unidades')->insert($chunk);
        }
    }
}
