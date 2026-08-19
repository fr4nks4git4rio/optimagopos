<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class TbConfigsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_configs')->delete();
        DB::table('tb_configs')->insert(['llave' => 'cfdi_timbrado_productivo', 'valor' => '0']);
        DB::table('tb_configs')->insert(['llave' => 'iva', 'valor' => '16']);
        DB::table('tb_configs')->insert(['llave' => 'precio_sucursal_adicional', 'valor' => '15']);
        DB::table('tb_configs')->insert(['llave' => 'precio_terminal_adicional', 'valor' => '15']);
        DB::table('tb_configs')->insert(['llave' => 'precio_usuario_adicional', 'valor' => '3']);
        DB::table('tb_configs')->insert(['llave' => 'moneda_sistema', 'valor' => 'MXN']);
        DB::table('tb_configs')->insert(['llave' => 'precio_timbre_adicional', 'valor' => '0.40']);
        DB::table('tb_configs')->insert(['llave' => 'precio_mes_analitica_basica_adicional', 'valor' => '10']);
    }
}
