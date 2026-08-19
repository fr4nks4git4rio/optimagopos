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
        Schema::create('tb_modulos', function (Blueprint $table) {

            $table->id();
            $table->string('icono', 50);
            $table->string('icono_color', 50);
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->integer('cant_funciones');
            $table->float('costo_base')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_modulos');
    }
};
