<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Concilia drift: la tabla tb_ticket_movimientos_caja existia creada fuera de
// migraciones sin `tipo_cambio` y con `sucursal_forma_pago_id` NOT NULL.
// Todos los cambios llevan guarda para ser no-op donde el esquema ya esta bien.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_ticket_movimientos_caja')) {
            return;
        }

        if (!Schema::hasColumn('tb_ticket_movimientos_caja', 'tipo_cambio')) {
            Schema::table('tb_ticket_movimientos_caja', function (Blueprint $table) {
                $table->decimal('tipo_cambio', 8, 4)->default(1.0000)->after('monto');
            });
        }

        if ($this->isNotNullable('tb_ticket_movimientos_caja', 'sucursal_forma_pago_id')) {
            DB::statement('ALTER TABLE `tb_ticket_movimientos_caja` MODIFY `sucursal_forma_pago_id` BIGINT UNSIGNED NULL');
        }

        if (!$this->hasForeignKey('tb_ticket_movimientos_caja', 'ticket_id')) {
            Schema::table('tb_ticket_movimientos_caja', function (Blueprint $table) {
                $table->foreign('ticket_id')->references('id')->on('tb_tickets');
            });
        }

        if (!$this->hasForeignKey('tb_ticket_movimientos_caja', 'sucursal_forma_pago_id')) {
            Schema::table('tb_ticket_movimientos_caja', function (Blueprint $table) {
                $table->foreign('sucursal_forma_pago_id')->references('id')->on('tb_sucursal_forma_pagos');
            });
        }
    }

    public function down(): void
    {
        // No revierte drift: solo eliminaria lo agregado por up().
        if (Schema::hasColumn('tb_ticket_movimientos_caja', 'tipo_cambio')) {
            Schema::table('tb_ticket_movimientos_caja', function (Blueprint $table) {
                $table->dropColumn('tipo_cambio');
            });
        }
    }

    private function isNotNullable(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row && $row->IS_NULLABLE === 'NO';
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS n FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );

        return $row && (int) $row->n > 0;
    }
};
