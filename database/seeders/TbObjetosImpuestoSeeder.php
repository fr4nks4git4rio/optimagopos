<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbObjetosImpuestoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_objetos_impuesto')->delete();

        $rows = [
            ['id' => 1, 'clave' => '01', 'descripcion' => 'No objeto de impuesto', 'activo' => 1, 'created_at' => '2022-03-29 21:02:40', 'updated_at' => null],
            ['id' => 2, 'clave' => '02', 'descripcion' => 'Si objeto de impuesto', 'activo' => 1, 'created_at' => '2022-03-29 21:02:55', 'updated_at' => null],
            ['id' => 3, 'clave' => '03', 'descripcion' => 'Si objeto del impuesto y no obligado al desglose.', 'activo' => 1, 'created_at' => '2022-03-29 21:03:07', 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_objetos_impuesto')->insert($chunk);
        }
    }
}
