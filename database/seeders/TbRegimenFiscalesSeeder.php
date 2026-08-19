<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbRegimenFiscalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_regimen_fiscales')->delete();

        $rows = [
            ['id' => 1, 'codigo' => '601', 'descripcion' => 'General de Ley Personas Morales', 'activo' => 1, 'created_at' => '2020-06-18 01:00:16', 'updated_at' => '2020-06-18 02:00:16'],
            ['id' => 2, 'codigo' => '603', 'descripcion' => 'Personas Morales con Fines no Lucrativos', 'activo' => 1, 'created_at' => '2020-06-18 02:00:39', 'updated_at' => '2020-06-18 02:00:39'],
            ['id' => 3, 'codigo' => '605', 'descripcion' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios', 'activo' => 1, 'created_at' => '2020-06-18 02:01:12', 'updated_at' => '2020-06-18 02:01:12'],
            ['id' => 4, 'codigo' => '606', 'descripcion' => 'Arrendamiento', 'activo' => 1, 'created_at' => '2020-06-18 02:11:49', 'updated_at' => '2020-06-18 02:11:49'],
            ['id' => 5, 'codigo' => '608', 'descripcion' => 'Demás ingresos', 'activo' => 1, 'created_at' => '2020-06-18 02:12:11', 'updated_at' => '2020-06-18 02:12:11'],
            ['id' => 6, 'codigo' => '609', 'descripcion' => 'Consolidación', 'activo' => 1, 'created_at' => '2020-06-18 02:12:39', 'updated_at' => '2020-06-18 02:12:39'],
            ['id' => 7, 'codigo' => '610', 'descripcion' => 'Residentes en el Extranjero sin Establecimiento Permanente en México', 'activo' => 1, 'created_at' => '2020-06-18 02:13:02', 'updated_at' => '2020-06-18 02:13:02'],
            ['id' => 8, 'codigo' => '611', 'descripcion' => 'Ingresos por Dividendos (socios y accionistas)', 'activo' => 1, 'created_at' => '2020-06-18 02:13:26', 'updated_at' => '2020-06-18 02:13:26'],
            ['id' => 9, 'codigo' => '612', 'descripcion' => 'Personas Fisicas con Actividades Empresariales y Profesionales', 'activo' => 1, 'created_at' => '2020-06-18 02:13:47', 'updated_at' => '2020-06-18 02:13:47'],
            ['id' => 10, 'codigo' => '614', 'descripcion' => 'Ingresos por intereses', 'activo' => 1, 'created_at' => '2020-06-18 02:14:05', 'updated_at' => '2020-06-18 02:14:05'],
            ['id' => 11, 'codigo' => '616', 'descripcion' => 'Sin obligaciones fiscales', 'activo' => 1, 'created_at' => '2020-06-18 02:14:23', 'updated_at' => '2020-06-18 02:14:23'],
            ['id' => 12, 'codigo' => '620', 'descripcion' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos', 'activo' => 1, 'created_at' => '2020-06-18 02:14:44', 'updated_at' => '2020-06-18 02:14:44'],
            ['id' => 13, 'codigo' => '621', 'descripcion' => 'Incorporación Fiscal', 'activo' => 1, 'created_at' => '2020-06-18 02:15:09', 'updated_at' => '2020-06-18 02:15:09'],
            ['id' => 14, 'codigo' => '622', 'descripcion' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras', 'activo' => 1, 'created_at' => '2020-06-18 02:15:30', 'updated_at' => '2020-06-18 02:15:30'],
            ['id' => 15, 'codigo' => '623', 'descripcion' => 'Opcional para Grupos de Sociedades', 'activo' => 1, 'created_at' => '2020-06-18 02:15:47', 'updated_at' => '2020-06-18 02:15:47'],
            ['id' => 16, 'codigo' => '624', 'descripcion' => 'Coordinados', 'activo' => 1, 'created_at' => '2020-06-18 02:16:06', 'updated_at' => '2020-06-18 02:16:06'],
            ['id' => 17, 'codigo' => '628', 'descripcion' => 'Hidrocarburos', 'activo' => 1, 'created_at' => '2020-06-18 02:16:26', 'updated_at' => '2020-06-18 02:16:26'],
            ['id' => 18, 'codigo' => '607', 'descripcion' => 'Régimen de Enajenación o Adquisición de Bienes', 'activo' => 1, 'created_at' => '2020-06-18 02:16:53', 'updated_at' => '2020-06-18 02:16:53'],
            ['id' => 19, 'codigo' => '629', 'descripcion' => 'De los Regímenes Fiscales Preferentes y de las Empresas Multinacionales', 'activo' => 1, 'created_at' => '2020-06-18 02:17:16', 'updated_at' => '2020-06-18 02:17:16'],
            ['id' => 20, 'codigo' => '630', 'descripcion' => 'Enajenación de acciones en bolsa de valores', 'activo' => 1, 'created_at' => '2020-06-18 02:17:34', 'updated_at' => '2020-06-18 02:17:34'],
            ['id' => 21, 'codigo' => '615', 'descripcion' => 'Régimen de los ingresos por obtención de premios', 'activo' => 1, 'created_at' => '2020-06-18 02:18:22', 'updated_at' => '2020-06-18 02:18:22'],
            ['id' => 22, 'codigo' => '605-1', 'descripcion' => 'Asalariado + Honorarios', 'activo' => 1, 'created_at' => '2024-12-06 18:02:41', 'updated_at' => '2024-12-06 18:02:59'],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_regimen_fiscales')->insert($chunk);
        }
    }
}
