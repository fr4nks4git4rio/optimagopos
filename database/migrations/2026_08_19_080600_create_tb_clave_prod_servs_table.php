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
        Schema::create('tb_clave_prod_servs', function (Blueprint $table) {

            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->float('valor_unitario');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('clave_unidad_id');
            $table->timestamps();

            $table->foreign('clave_unidad_id')->references('id')->on('tb_clave_unidades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_clave_prod_servs');
    }
};
