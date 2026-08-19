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
        Schema::create('tb_motivos_cancelacion_factura', function (Blueprint $table) {

            $table->id();
            $table->string('codigo', 50)->default('');
            $table->string('descripcion')->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_motivos_cancelacion_factura');
    }
};
