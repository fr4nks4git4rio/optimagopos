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
        Schema::create('tb_tipo_cambios', function (Blueprint $table) {

            $table->id();
            $table->float('tasa', 8, 4);
            $table->unsignedBigInteger('from_id');
            $table->unsignedBigInteger('to_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('cliente_id');
            $table->timestamps();

            $table->unique(['from_id', 'to_id', 'sucursal_id', 'cliente_id'], 'from_id_to_id_sucursal_id_cliente_id');
            $table->foreign('cliente_id')->references('id')->on('tb_clientes');
            $table->foreign('from_id')->references('id')->on('tb_monedas');
            $table->foreign('to_id')->references('id')->on('tb_monedas');
            $table->foreign('sucursal_id')->references('id')->on('tb_sucursales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_tipo_cambios');
    }
};
