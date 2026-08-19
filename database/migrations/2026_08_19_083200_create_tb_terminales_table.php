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
        Schema::create('tb_terminales', function (Blueprint $table) {

            $table->id();
            $table->integer('id_pos');
            $table->string('identificador', 50);
            $table->string('nombre', 100);
            $table->text('comentarios')->nullable();
            $table->boolean('es_vk')->default(false);
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('suscripcion_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sucursal_id')->references('id')->on('tb_sucursales');
            $table->foreign('suscripcion_id')->references('id')->on('tb_suscripciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_terminales');
    }
};
