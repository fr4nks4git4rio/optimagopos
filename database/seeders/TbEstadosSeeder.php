<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbEstadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_estados')->delete();

        $rows = [
            ['id' => 1, 'codigo' => 'AGU', 'nombre' => 'Aguascalientes ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 2, 'codigo' => 'BCN', 'nombre' => 'Baja California ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 3, 'codigo' => 'BCS', 'nombre' => 'Baja California Sur ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 4, 'codigo' => 'CAM', 'nombre' => 'Campeche ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 5, 'codigo' => 'CHP', 'nombre' => 'Chiapas ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 6, 'codigo' => 'CHH', 'nombre' => 'Chihuahua ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 7, 'codigo' => 'COA', 'nombre' => 'Coahuila de Zaragoza ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 8, 'codigo' => 'COL', 'nombre' => 'Colima ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 9, 'codigo' => 'DUR', 'nombre' => 'Durango ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 10, 'codigo' => 'GUA', 'nombre' => 'Guanajuato ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-12-09 23:53:18'],
            ['id' => 11, 'codigo' => 'GRO', 'nombre' => 'Guerrero ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 12, 'codigo' => 'HGO', 'nombre' => 'Hidalgo ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 13, 'codigo' => 'JAL', 'nombre' => 'Jalisco ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 14, 'codigo' => 'MEX', 'nombre' => 'Mexico (Estado de México) ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 15, 'codigo' => 'MIC', 'nombre' => 'Michoacán de Ocampo ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 16, 'codigo' => 'MOR', 'nombre' => 'Morelos ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 17, 'codigo' => 'NAY', 'nombre' => 'Nayarit ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 18, 'codigo' => 'NLE', 'nombre' => 'Nuevo León ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 19, 'codigo' => 'OAX', 'nombre' => 'Oaxaca ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 20, 'codigo' => 'PUE', 'nombre' => 'Puebla ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-12-20 01:20:23'],
            ['id' => 21, 'codigo' => 'QUE', 'nombre' => 'Querétaro ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 22, 'codigo' => 'ROO', 'nombre' => 'Quintana Roo ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 23, 'codigo' => 'SLP', 'nombre' => 'San Luis Potosí ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 24, 'codigo' => 'SIN', 'nombre' => 'Sinaloa ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 25, 'codigo' => 'SON', 'nombre' => 'Sonora ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 26, 'codigo' => 'TAB', 'nombre' => 'Tabasco ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 27, 'codigo' => 'TAM', 'nombre' => 'Tamaulipas ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 28, 'codigo' => 'TLA', 'nombre' => 'Tlaxcala ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 29, 'codigo' => 'VER', 'nombre' => 'Veracruz de Ignacio de la Llave ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 30, 'codigo' => 'YUC', 'nombre' => 'Yucatán ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 31, 'codigo' => 'ZAC', 'nombre' => 'Zacatecas ', 'activo' => 1, 'created_at' => '2024-11-20 00:46:56', 'updated_at' => '2024-11-20 00:46:56'],
            ['id' => 32, 'codigo' => 'ZUI', 'nombre' => 'ZUIZANTUN', 'activo' => 1, 'created_at' => '2024-12-06 04:26:42', 'updated_at' => '2024-12-06 04:27:08'],
            ['id' => 33, 'codigo' => 'VERAAA', 'nombre' => 'VERACRUUUU', 'activo' => 0, 'created_at' => '2024-12-20 01:17:03', 'updated_at' => '2024-12-20 01:18:48'],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_estados')->insert($chunk);
        }
    }
}
