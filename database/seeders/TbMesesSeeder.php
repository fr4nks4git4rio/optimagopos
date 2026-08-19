<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbMesesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_meses')->delete();

        $rows = [
            ['id' => 1, 'clave' => '01', 'descripcion' => 'Enero', 'created_at' => '2022-03-29 20:10:35', 'updated_at' => null],
            ['id' => 2, 'clave' => '02', 'descripcion' => 'Febrero', 'created_at' => '2022-03-29 20:10:45', 'updated_at' => null],
            ['id' => 3, 'clave' => '03', 'descripcion' => 'Marzo', 'created_at' => '2022-03-29 20:10:55', 'updated_at' => null],
            ['id' => 4, 'clave' => '04', 'descripcion' => 'Abril', 'created_at' => '2022-03-29 20:11:04', 'updated_at' => null],
            ['id' => 5, 'clave' => '05', 'descripcion' => 'Mayo', 'created_at' => '2022-03-29 20:11:11', 'updated_at' => null],
            ['id' => 6, 'clave' => '06', 'descripcion' => 'Junio', 'created_at' => '2022-03-29 20:11:23', 'updated_at' => null],
            ['id' => 7, 'clave' => '07', 'descripcion' => 'Julio', 'created_at' => '2022-03-29 20:11:30', 'updated_at' => null],
            ['id' => 8, 'clave' => '08', 'descripcion' => 'Agosto', 'created_at' => '2022-03-29 20:11:48', 'updated_at' => null],
            ['id' => 9, 'clave' => '09', 'descripcion' => 'Septiembre', 'created_at' => '2022-03-29 20:11:56', 'updated_at' => null],
            ['id' => 10, 'clave' => '10', 'descripcion' => 'Octubre', 'created_at' => '2022-03-29 20:12:02', 'updated_at' => null],
            ['id' => 11, 'clave' => '11', 'descripcion' => 'Noviembre', 'created_at' => '2022-03-29 20:12:16', 'updated_at' => null],
            ['id' => 12, 'clave' => '12', 'descripcion' => 'Diciembre', 'created_at' => '2022-03-29 20:12:26', 'updated_at' => null],
            ['id' => 13, 'clave' => '13', 'descripcion' => 'Enero-Febrero', 'created_at' => '2022-03-29 20:12:41', 'updated_at' => null],
            ['id' => 14, 'clave' => '14', 'descripcion' => 'Marzo-Abril', 'created_at' => '2022-03-29 20:12:51', 'updated_at' => null],
            ['id' => 15, 'clave' => '15', 'descripcion' => 'Mayo-Junio', 'created_at' => '2022-03-29 20:13:09', 'updated_at' => null],
            ['id' => 16, 'clave' => '16', 'descripcion' => 'Julio-Agosto', 'created_at' => '2022-03-29 20:13:17', 'updated_at' => null],
            ['id' => 17, 'clave' => '17', 'descripcion' => 'Septiembre-Octubre', 'created_at' => '2022-03-29 20:13:31', 'updated_at' => null],
            ['id' => 18, 'clave' => '18', 'descripcion' => 'Noviembre-Diciembre', 'created_at' => '2022-03-29 20:13:47', 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_meses')->insert($chunk);
        }
    }
}
