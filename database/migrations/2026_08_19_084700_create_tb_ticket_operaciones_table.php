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
        Schema::create('tb_ticket_operaciones', function (Blueprint $table) {

            $table->id();
            $table->string('nombre', 100);
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->decimal('descuento', 10, 2)->nullable()->default(0.00);
            $table->decimal('propina', 10, 2)->default(0.00);
            $table->decimal('tipo_cambio', 8, 4)->default(1.0000);
            $table->boolean('es_cambio')->default(false);
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('sucursal_forma_pago_id')->nullable();
            $table->unsignedBigInteger('factura_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->timestamps();

            $table->foreign('empleado_id')->references('id')->on('tb_empleados');
            $table->foreign('factura_id')->references('id')->on('tb_facturas');
            $table->foreign('sucursal_forma_pago_id')->references('id')->on('tb_sucursal_forma_pagos');
            $table->foreign('ticket_id')->references('id')->on('tb_tickets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ticket_operaciones');
    }
};
