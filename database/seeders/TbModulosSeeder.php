<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TbModulosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_modulos')->delete();

        $rows = [
            ['id' => 1, 'icono' => 'bi-cloud-fill', 'icono_color' => '#58a8fe', 'nombre' => 'Cloud Básico', 'descripcion' => 'Acceso plataforma, usuarios, respaldo', 'cant_funciones' => 1, 'costo_base' => 10, 'created_at' => '2026-06-30 01:40:22', 'updated_at' => '2026-06-30 08:14:57', 'deleted_at' => null],
            ['id' => 2, 'icono' => 'bi-search', 'icono_color' => '#3eac51', 'nombre' => 'Operaciones (VK)', 'descripcion' => 'Monitoreo de operaciones en tiempo real', 'cant_funciones' => 1, 'costo_base' => 30, 'created_at' => '2026-06-30 01:41:44', 'updated_at' => '2026-06-30 08:15:17', 'deleted_at' => null],
            ['id' => 3, 'icono' => 'bi-file-pdf', 'icono_color' => '#e6b54c', 'nombre' => 'Facturación', 'descripcion' => 'Facturación SAT / CFDI', 'cant_funciones' => 1, 'costo_base' => 15, 'created_at' => '2026-06-30 01:43:15', 'updated_at' => '2026-06-30 08:15:01', 'deleted_at' => null],
            ['id' => 4, 'icono' => 'bi-graph-up', 'icono_color' => '#4cb8e6', 'nombre' => 'Analítica Básica', 'descripcion' => 'Reportes y dashboard estándar', 'cant_funciones' => 1, 'costo_base' => 20, 'created_at' => '2026-06-30 01:45:59', 'updated_at' => '2026-06-30 08:13:59', 'deleted_at' => null],
            ['id' => 5, 'icono' => 'bi-graph-up-arrow', 'icono_color' => '#d44ce6', 'nombre' => 'Analítica Avanzada', 'descripcion' => 'Reportes avanzados e inteligencia', 'cant_funciones' => 1, 'costo_base' => 10, 'created_at' => '2026-06-30 01:46:44', 'updated_at' => '2026-06-30 08:13:37', 'deleted_at' => null],
            ['id' => 6, 'icono' => 'bi-camera-reels-fill', 'icono_color' => '#e64c63', 'nombre' => 'CCTV / Seguridad', 'descripcion' => 'Monitoreo de cámaras y eventos', 'cant_funciones' => 1, 'costo_base' => 30, 'created_at' => '2026-06-30 01:48:04', 'updated_at' => '2026-06-30 08:14:52', 'deleted_at' => null],
            ['id' => 7, 'icono' => 'bi-stars', 'icono_color' => '#4f46e5', 'nombre' => 'IA / Predictivo', 'descripcion' => 'Predicción de ventas, alertas, etc.', 'cant_funciones' => 1, 'costo_base' => 22, 'created_at' => '2026-06-30 01:50:04', 'updated_at' => '2026-06-30 08:15:06', 'deleted_at' => null],
            ['id' => 8, 'icono' => 'bi-fingerprint', 'icono_color' => '#12a30f', 'nombre' => 'Auditoría', 'descripcion' => 'Bitácoras, auditoría avanzada', 'cant_funciones' => 1, 'costo_base' => 10, 'created_at' => '2026-06-30 01:52:11', 'updated_at' => '2026-06-30 08:14:47', 'deleted_at' => null],
            ['id' => 9, 'icono' => 'bi-diagram-3-fill', 'icono_color' => '#e6bd4c', 'nombre' => 'Multi-sucursal Avanzado', 'descripcion' => 'Consolidado corporativo avanzado', 'cant_funciones' => 1, 'costo_base' => 40, 'created_at' => '2026-06-30 01:53:31', 'updated_at' => '2026-06-30 08:15:11', 'deleted_at' => null],
            ['id' => 10, 'icono' => 'bi-ethernet', 'icono_color' => '#8f8f94', 'nombre' => 'API / Integraciones', 'descripcion' => 'Integración con terceros / ERP', 'cant_funciones' => 1, 'costo_base' => 10, 'created_at' => '2026-06-30 01:54:44', 'updated_at' => '2026-07-02 19:55:13', 'deleted_at' => null],
            ['id' => 11, 'icono' => 'bi-hdd', 'icono_color' => '#e64c96', 'nombre' => 'Almacenamiento Extra', 'descripcion' => 'Almacenamiento adicional en la nube', 'cant_funciones' => 1, 'costo_base' => 10, 'created_at' => '2026-06-30 01:56:24', 'updated_at' => '2026-06-30 08:13:17', 'deleted_at' => null],
        ];

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('tb_modulos')->insert($chunk);
        }
    }
}
