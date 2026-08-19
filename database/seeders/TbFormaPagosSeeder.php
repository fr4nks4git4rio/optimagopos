<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbFormaPagosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_forma_pagos')->delete();

        $rows = [
            ['id' => 1, 'codigo' => '99', 'descripcion' => 'Por definir', 'activo' => 1, 'created_at' => '2020-06-18 00:14:13', 'updated_at' => '2020-06-18 01:14:13'],
            ['id' => 2, 'codigo' => '32', 'descripcion' => 'Tarjeta', 'activo' => 1, 'created_at' => '2020-06-18 01:14:38', 'updated_at' => '2020-06-18 01:14:38'],
            ['id' => 3, 'codigo' => '01', 'descripcion' => 'Efectivo', 'activo' => 1, 'created_at' => '2020-06-18 01:15:22', 'updated_at' => '2020-06-18 01:15:22'],
            ['id' => 4, 'codigo' => '02', 'descripcion' => 'Cheque nominativo', 'activo' => 1, 'created_at' => '2020-06-18 01:15:39', 'updated_at' => '2020-06-18 01:15:39'],
            ['id' => 5, 'codigo' => '03', 'descripcion' => 'Transferencia electrónica de fondos', 'activo' => 1, 'created_at' => '2020-06-18 01:16:09', 'updated_at' => '2020-06-18 01:16:09'],
            ['id' => 6, 'codigo' => '04', 'descripcion' => 'Tarjeta de crédito', 'activo' => 1, 'created_at' => '2020-06-18 01:16:35', 'updated_at' => '2020-06-18 01:16:35'],
            ['id' => 7, 'codigo' => '05', 'descripcion' => 'Monedero electrónico', 'activo' => 1, 'created_at' => '2020-06-18 01:16:51', 'updated_at' => '2020-06-18 01:16:51'],
            ['id' => 8, 'codigo' => '06', 'descripcion' => 'Dinero electrónico', 'activo' => 1, 'created_at' => '2020-06-18 01:17:33', 'updated_at' => '2020-06-18 01:17:33'],
            ['id' => 9, 'codigo' => '30', 'descripcion' => 'Aplicación de anticipo', 'activo' => 1, 'created_at' => '2022-10-05 17:45:06', 'updated_at' => '2024-12-06 17:48:10'],
            ['id' => 10, 'codigo' => '08', 'descripcion' => 'Vales de despensa', 'activo' => 1, 'created_at' => '2024-12-06 17:47:58', 'updated_at' => '2024-12-06 17:47:58'],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_forma_pagos')->insert($chunk);
        }
    }
}
