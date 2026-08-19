<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_roles')->delete();

        $rows = [
            ['id' => 1, 'codigo' => 'super_admin', 'nombre' => 'Super Administrador', 'descripcion' => null, 'created_at' => '2025-05-12 20:56:14', 'updated_at' => null],
            ['id' => 2, 'codigo' => 'admin', 'nombre' => 'Administrador', 'descripcion' => null, 'created_at' => '2025-05-12 20:56:27', 'updated_at' => null],
            ['id' => 3, 'codigo' => 'accountant', 'nombre' => 'Contabilidad', 'descripcion' => null, 'created_at' => '2026-06-30 08:50:39', 'updated_at' => null],
            ['id' => 4, 'codigo' => 'manager', 'nombre' => 'Gerente', 'descripcion' => null, 'created_at' => '2026-08-19 19:19:16', 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_roles')->insert($chunk);
        }
    }
}
