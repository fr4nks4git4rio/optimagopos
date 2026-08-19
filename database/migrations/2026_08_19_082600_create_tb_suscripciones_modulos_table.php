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
        Schema::create('tb_suscripciones_modulos', function (Blueprint $table) {

            $table->unsignedBigInteger('suscripcion_id');
            $table->unsignedBigInteger('modulo_id');
            $table->primary(['suscripcion_id', 'modulo_id']);

            $table->foreign('suscripcion_id')->references('id')->on('tb_suscripciones');
            $table->foreign('modulo_id')->references('id')->on('tb_modulos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_suscripciones_modulos');
    }
};
