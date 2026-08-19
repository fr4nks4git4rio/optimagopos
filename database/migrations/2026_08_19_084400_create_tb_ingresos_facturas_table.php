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
        Schema::create('tb_ingresos_facturas', function (Blueprint $table) {

            $table->unsignedBigInteger('factura_id');
            $table->unsignedBigInteger('ingreso_id');
            $table->unsignedBigInteger('nota_credito_id')->nullable();
            $table->decimal('monto', 9, 2);
            $table->decimal('monto_moneda_original', 9, 2);
            $table->enum('moneda', ['MXN', 'USD'])->nullable();
            $table->primary(['factura_id', 'ingreso_id']);

            $table->foreign('factura_id')->references('id')->on('tb_facturas');
            $table->foreign('nota_credito_id')->references('id')->on('tb_facturas');
            $table->foreign('ingreso_id')->references('id')->on('tb_ingresos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ingresos_facturas');
    }
};
