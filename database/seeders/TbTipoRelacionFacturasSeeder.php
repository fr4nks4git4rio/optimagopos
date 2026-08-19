<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbTipoRelacionFacturasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_tipo_relacion_facturas')->delete();

        $rows = [
            ['id' => 1, 'codigo' => '01', 'descripcion' => 'Nota de crédito de los documentos relacionados', 'created_at' => null, 'updated_at' => null],
            ['id' => 2, 'codigo' => '02', 'descripcion' => 'Nota de débito de los documentos relacionados', 'created_at' => null, 'updated_at' => null],
            ['id' => 3, 'codigo' => '03', 'descripcion' => 'Devolución de mercancía sobre facturas o traslados previos', 'created_at' => null, 'updated_at' => null],
            ['id' => 4, 'codigo' => '04', 'descripcion' => 'Sustitución de los CFDI previos', 'created_at' => null, 'updated_at' => null],
            ['id' => 5, 'codigo' => '05', 'descripcion' => 'Traslados de mercancías facturados previamente', 'created_at' => null, 'updated_at' => null],
            ['id' => 6, 'codigo' => '06', 'descripcion' => 'Factura generada por los traslados previos', 'created_at' => null, 'updated_at' => null],
            ['id' => 7, 'codigo' => '07', 'descripcion' => 'CFDI por aplicación de anticipo', 'created_at' => null, 'updated_at' => null],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_tipo_relacion_facturas')->insert($chunk);
        }
    }
}
