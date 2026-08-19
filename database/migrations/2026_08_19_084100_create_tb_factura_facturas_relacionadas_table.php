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
        Schema::create('tb_factura_facturas_relacionadas', function (Blueprint $table) {

            $table->unsignedBigInteger('factura_id');
            $table->unsignedBigInteger('factura_relacionada_id');
            $table->primary(['factura_id', 'factura_relacionada_id']);

            $table->foreign('factura_id')->references('id')->on('tb_facturas');
            $table->foreign('factura_relacionada_id')->references('id')->on('tb_facturas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_factura_facturas_relacionadas');
    }
};
