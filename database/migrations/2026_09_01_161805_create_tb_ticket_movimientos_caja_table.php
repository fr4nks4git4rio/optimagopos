<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Idempotente: en algunas BD la tabla ya existe creada fuera de migraciones
        // (con esquema parcial). El drift se concilia en 2026_09_03_000002.
        if (Schema::hasTable('tb_ticket_movimientos_caja')) {
            return;
        }
        Schema::create('tb_ticket_movimientos_caja', function (Blueprint $table) {

            $table->id();
            $table->string('nombre', 100);
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->decimal('tipo_cambio', 8, 4)->default(1.0000);
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('sucursal_forma_pago_id')->nullable();
            $table->timestamps();

            $table->foreign('sucursal_forma_pago_id')->references('id')->on('tb_sucursal_forma_pagos');
            $table->foreign('ticket_id')->references('id')->on('tb_tickets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ticket_movimientos_caja');
    }
};
