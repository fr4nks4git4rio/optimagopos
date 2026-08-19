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
        Schema::create('tb_paquetes', function (Blueprint $table) {

            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->float('precio')->default(0);
            $table->integer('cant_sucursales');
            $table->integer('cant_terminales');
            $table->integer('cant_usuarios');
            $table->integer('cant_timbres')->nullable();
            $table->integer('cant_meses_analitica_basica')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_paquetes');
    }
};
