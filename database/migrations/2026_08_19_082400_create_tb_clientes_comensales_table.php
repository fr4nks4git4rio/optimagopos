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
        Schema::create('tb_clientes_comensales', function (Blueprint $table) {

            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('comensal_id');
            $table->boolean('activo')->default(true);
            $table->primary(['cliente_id', 'comensal_id']);

            $table->foreign('cliente_id')->references('id')->on('tb_clientes');
            $table->foreign('comensal_id')->references('id')->on('tb_clientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_clientes_comensales');
    }
};
