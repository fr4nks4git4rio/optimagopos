<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class TbUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_usuarios')->delete();
        DB::table('tb_usuarios')->insert([
            'id' => 1,
            'nombre' => 'Super Admin',
            'apellidos' => 'Administrador',
            'email' => 'erp@wifiempresarial.com',
            'password' => 'Cancun2026*',
            'lang' => 'es',
            'rol_id' => 1,
            'created_at' => now()->format('Y-m-d H:i:s')
        ]);
    }
}
