<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class TbOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_direcciones')->delete();
        DB::table('tb_direcciones')->insert(['id' => 1, 'codigo_postal' => '77533', 'created_at' => now()->format('Y-m-d H:i:s')]);

        DB::table('tb_clientes')->delete();
        DB::table('tb_clientes')->insert([
            'id' => 1,
            'nombre_comercial' => Crypt::encrypt('Wifi Empresarial'),
            'razon_social' => Crypt::encrypt('COMERCIO ELECTRONICO DOMINGUEZ & BALLESTER'),
            'rfc' => 'CED160706EU8',
            'correo' => Crypt::encrypt('erp@wifiempresarial.com'),
            'telefono' => Crypt::encrypt('9988479278'),
            'contacto_nombre' => Crypt::encrypt('Reinier Ballester'),
            'contacto_correo' => Crypt::encrypt('erp@wifiempresarial.com'),
            'contacto_telefono' => Crypt::encrypt('9988479278'),
            'es_propietario' => 1,
            'regimen_fiscal_id' => 1,
            'direccion_fiscal_id' => 1,
            'created_at' => now()->format('Y-m-d H:i:s')
        ]);
    }
}
