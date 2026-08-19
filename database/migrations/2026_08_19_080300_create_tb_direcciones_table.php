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
        Schema::create('tb_direcciones', function (Blueprint $table) {

            $table->id();
            $table->string('calle', 50)->nullable();
            $table->string('no_exterior', 20)->nullable();
            $table->string('no_interior', 20)->nullable();
            $table->string('codigo_postal', 50)->nullable();
            $table->string('colonia', 100)->nullable();
            $table->unsignedInteger('localidad_id')->nullable();
            $table->unsignedInteger('municipio_id')->nullable();
            $table->unsignedInteger('estado_id')->nullable();
            $table->text('referencia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_direcciones');
    }
};
