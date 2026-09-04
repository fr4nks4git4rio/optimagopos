<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fase 2: indices para FacturaController (cuentas por cobrar y listados).
// Soportan los filtros sargables: estado/nota/complemento/sistema + fechas,
// cliente, folio (LIKE '%..%'), moneda y rango de total.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_facturas', function (Blueprint $table) {
            $table->index(
                ['estado', 'es_nota_credito', 'es_complemento', 'del_sistema', 'fecha_certificacion'],
                'tb_facturas_filtros_idx'
            );
            $table->index(['cliente_id', 'estado'], 'tb_facturas_cli_est_idx');
            $table->index('folio_interno', 'tb_facturas_folio_idx');
            $table->index('moneda', 'tb_facturas_moneda_idx');
            $table->index('total', 'tb_facturas_total_idx');
            $table->index('fecha_emision', 'tb_facturas_emision_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tb_facturas', function (Blueprint $table) {
            $table->dropIndex('tb_facturas_filtros_idx');
            $table->dropIndex('tb_facturas_cli_est_idx');
            $table->dropIndex('tb_facturas_folio_idx');
            $table->dropIndex('tb_facturas_moneda_idx');
            $table->dropIndex('tb_facturas_total_idx');
            $table->dropIndex('tb_facturas_emision_idx');
        });
    }
};
