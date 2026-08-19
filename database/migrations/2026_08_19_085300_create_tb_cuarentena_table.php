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
        Schema::create('tb_cuarentena', function (Blueprint $table) {

            $table->id();
            $table->string('texto');
            $table->string('ip', 30)->nullable();
            $table->longText('data');
            $table->boolean('es_vk')->default(false);
            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('tb_clientes');
            $table->foreign('sucursal_id')->references('id')->on('tb_sucursales');
            $table->foreign('terminal_id')->references('id')->on('tb_terminales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_cuarentena');
    }
};
