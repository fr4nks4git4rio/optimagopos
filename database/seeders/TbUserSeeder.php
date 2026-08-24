<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'password' => Hash::make('Cancun2026*'),
            'lang' => 'es',
            'cliente_id' => 1,
            'created_at' => now()->format('Y-m-d H:i:s')
        ]);

        $user = User::find(1);
        $user->assignRole('SuperAdmin');
    }
}
