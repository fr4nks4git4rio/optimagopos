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
        Schema::create('tb_usuarios_sucursales', function (Blueprint $table) {

            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->primary(['usuario_id', 'sucursal_id']);

            $table->foreign('usuario_id')->references('id')->on('tb_usuarios');
            $table->foreign('sucursal_id')->references('id')->on('tb_sucursales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_usuarios_sucursales');
    }
};
