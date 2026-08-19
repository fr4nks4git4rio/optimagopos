<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbPeriodicidadesFacturaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_periodicidades_factura')->delete();

        $rows = [
            ['id' => 1, 'clave' => '01', 'descripcion' => 'Diario', 'created_at' => '2022-03-29 20:14:24', 'updated_at' => null],
            ['id' => 2, 'clave' => '02', 'descripcion' => 'Semanal', 'created_at' => '2022-03-29 20:14:35', 'updated_at' => null],
            ['id' => 3, 'clave' => '03', 'descripcion' => 'Quincenal', 'created_at' => '2022-03-29 20:14:47', 'updated_at' => null],
            ['id' => 4, 'clave' => '04', 'descripcion' => 'Mensual', 'created_at' => '2022-03-29 20:14:54', 'updated_at' => null],
            ['id' => 5, 'clave' => '05', 'descripcion' => 'Bimestral', 'created_at' => '2022-03-29 20:15:07', 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_periodicidades_factura')->insert($chunk);
        }
    }
}
