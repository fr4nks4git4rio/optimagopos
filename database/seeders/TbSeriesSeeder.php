<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbSeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_series')->delete();

        $rows = [
            ['id' => 1, 'descripcion' => 'F', 'activo' => 1, 'created_at' => '2021-02-12 15:18:09', 'updated_at' => '2021-02-12 15:18:09'],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_series')->insert($chunk);
        }
    }
}
