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
        Schema::create('tb_facturas_complementos', function (Blueprint $table) {

            $table->unsignedBigInteger('factura_id');
            $table->unsignedBigInteger('complemento_id');
            $table->integer('no_parcialidad')->nullable();
            $table->decimal('balance_previo', 20, 2)->nullable();
            $table->decimal('importe_pagado', 20, 2)->nullable();
            $table->primary(['factura_id', 'complemento_id']);

            $table->foreign('factura_id')->references('id')->on('tb_facturas');
            $table->foreign('complemento_id')->references('id')->on('tb_facturas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_facturas_complementos');
    }
};
