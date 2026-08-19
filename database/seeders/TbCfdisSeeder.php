<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbCfdisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_cfdis')->delete();

        $rows = [
            ['id' => 1, 'codigo' => 'G01', 'descripcion' => 'Adquisición de mercancías', 'activo' => 1, 'created_at' => '2021-01-18 15:15:39', 'updated_at' => '2021-01-18 15:15:39'],
            ['id' => 2, 'codigo' => 'G02', 'descripcion' => 'Devoluciones, descuentos o bonificaciones', 'activo' => 1, 'created_at' => '2021-01-18 15:16:12', 'updated_at' => '2021-01-18 15:16:12'],
            ['id' => 3, 'codigo' => 'G03', 'descripcion' => 'Gastos en general', 'activo' => 1, 'created_at' => '2021-01-18 15:16:43', 'updated_at' => '2021-01-18 15:16:43'],
            ['id' => 4, 'codigo' => 'I04', 'descripcion' => 'Equipo de computo y accesorios', 'activo' => 1, 'created_at' => '2021-01-18 15:17:29', 'updated_at' => '2021-01-18 15:17:29'],
            ['id' => 5, 'codigo' => 'I08', 'descripcion' => 'Otra maquinaria y equipo', 'activo' => 1, 'created_at' => '2021-01-18 15:18:11', 'updated_at' => '2021-01-18 15:18:11'],
            ['id' => 6, 'codigo' => 'P01', 'descripcion' => 'Por definir', 'activo' => 1, 'created_at' => '2021-01-18 15:19:28', 'updated_at' => '2021-01-18 15:19:28'],
            ['id' => 7, 'codigo' => 'S01', 'descripcion' => 'Sin efectos fiscales', 'activo' => 1, 'created_at' => '2024-12-06 17:43:24', 'updated_at' => '2024-12-08 15:06:24'],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_cfdis')->insert($chunk);
        }
    }
}
