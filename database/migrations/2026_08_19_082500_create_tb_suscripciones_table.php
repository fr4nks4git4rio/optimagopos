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
        Schema::create('tb_suscripciones', function (Blueprint $table) {

            $table->id();
            $table->integer('cant_sucursales');
            $table->integer('cant_terminales');
            $table->integer('cant_usuarios');
            $table->integer('cant_timbres')->nullable();
            $table->integer('cant_meses_analitica_basica')->nullable();
            $table->date('fecha_inicio_operaciones')->nullable();
            $table->date('fecha_inicio_pagos')->nullable();
            $table->enum('periodicidad_pagos', ['MENSUAL', 'BIMESTRAL', 'TRIMESTRAL', 'SEMESTRAL', 'ANUAL'])->default('MENSUAL');
            $table->decimal('precio_paquete', 10, 2)->default(0.00);
            $table->decimal('precio_extra', 10, 2)->default(0.00);
            $table->decimal('precio_total', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['PENDIENTE', 'ACTIVA', 'VENCIDA', 'INACTIVA'])->default('PENDIENTE');
            $table->text('motivo_desactivacion')->nullable();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('paquete_id')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('tb_clientes');
            $table->foreign('paquete_id')->references('id')->on('tb_paquetes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_suscripciones');
    }
};
