<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fase 1 rendimiento: indices para dashboard Home y reportes (Historico, VentasDiarias).
// Las FK ya crean indice en su propia columna; aqui se agregan compuestos de
// (sucursal/terminal + fecha_transaccion) que usan los filtros sargables de
// commonWhere() y de los reportes. Solo lectura/escritura normal: cada indice
// acelera lecturas y encarece un poco los INSERT (aceptado: tb_* es lectura pesada).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_tickets', function (Blueprint $table) {
            $table->index(['sucursal_id', 'fecha_transaccion'], 'tb_tickets_suc_fecha_idx');
            $table->index(['terminal_id', 'fecha_transaccion'], 'tb_tickets_term_fecha_idx');
            $table->index(['modo_entrenamiento', 'fecha_transaccion'], 'tb_tickets_modo_fecha_idx');
            $table->index('id_transaccion', 'tb_tickets_id_trans_idx');
            $table->index('importe', 'tb_tickets_importe_idx');
        });

        Schema::table('tb_ticket_productos', function (Blueprint $table) {
            $table->index(['ticket_id', 'producto_id'], 'tb_ticket_prod_tick_prod_idx');
        });

        Schema::table('tb_ticket_producto_correcciones', function (Blueprint $table) {
            $table->index(['ticket_id', 'nombre'], 'tb_ticket_corr_tick_nom_idx');
        });

        Schema::table('tb_tickets_vk', function (Blueprint $table) {
            $table->index(['sucursal_id', 'estado', 'fecha_transaccion'], 'tb_tickets_vk_suc_est_fecha_idx');
            $table->index(['terminal_id', 'estado'], 'tb_tickets_vk_term_est_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tb_tickets_vk', function (Blueprint $table) {
            $table->dropIndex('tb_tickets_vk_suc_est_fecha_idx');
            $table->dropIndex('tb_tickets_vk_term_est_idx');
        });

        Schema::table('tb_ticket_producto_correcciones', function (Blueprint $table) {
            $table->dropIndex('tb_ticket_corr_tick_nom_idx');
        });

        Schema::table('tb_ticket_productos', function (Blueprint $table) {
            $table->dropIndex('tb_ticket_prod_tick_prod_idx');
        });

        Schema::table('tb_tickets', function (Blueprint $table) {
            $table->dropIndex('tb_tickets_suc_fecha_idx');
            $table->dropIndex('tb_tickets_term_fecha_idx');
            $table->dropIndex('tb_tickets_modo_fecha_idx');
            $table->dropIndex('tb_tickets_id_trans_idx');
            $table->dropIndex('tb_tickets_importe_idx');
        });
    }
};
