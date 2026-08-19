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
        Schema::create('tb_sucursal_forma_pagos', function (Blueprint $table) {

            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('moneda_id');
            $table->unsignedBigInteger('forma_pago_id')->nullable();
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('forma_pago_id')->references('id')->on('tb_forma_pagos');
            $table->foreign('moneda_id')->references('id')->on('tb_monedas');
            $table->foreign('sucursal_id')->references('id')->on('tb_sucursales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_sucursal_forma_pagos');
    }
};
