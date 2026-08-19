<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbMonedasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_monedas')->delete();

        $rows = [
            ['id' => 1, 'acronimo' => 'MXN', 'nombre' => 'Peso Mexicano', 'created_at' => '2026-05-21 01:24:55', 'updated_at' => null, 'deleted_at' => null],
            ['id' => 2, 'acronimo' => 'USD', 'nombre' => 'Dólar Estadounidense', 'created_at' => '2026-05-21 01:25:10', 'updated_at' => null, 'deleted_at' => null],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_monedas')->insert($chunk);
        }
    }
}
