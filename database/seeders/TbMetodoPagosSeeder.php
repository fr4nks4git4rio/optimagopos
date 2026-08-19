<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbMetodoPagosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_metodo_pagos')->delete();

        $rows = [
            ['id' => 1, 'codigo' => 'PUE', 'descripcion' => 'Pago en una sola exhibición', 'activo' => 1, 'created_at' => '2020-06-18 00:18:40', 'updated_at' => '2020-06-18 01:18:40'],
            ['id' => 2, 'codigo' => 'PPD', 'descripcion' => 'Pago en parcialidades o diferido', 'activo' => 1, 'created_at' => '2020-06-18 01:19:16', 'updated_at' => '2020-06-18 01:19:16'],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_metodo_pagos')->insert($chunk);
        }
    }
}
