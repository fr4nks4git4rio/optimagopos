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
        Schema::create('tb_modificadores_vk', function (Blueprint $table) {

            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('sucursal_id');
            $table->timestamps();

            $table->foreign('sucursal_id')->references('id')->on('tb_sucursales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_modificadores_vk');
    }
};
