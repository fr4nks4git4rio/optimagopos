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
        Schema::create('tb_tickets_vk', function (Blueprint $table) {

            $table->id();
            $table->string('mesa', 50)->nullable();
            $table->string('asiento', 50)->nullable();
            $table->dateTime('fecha_transaccion')->nullable();
            $table->dateTime('fecha_en_proceso')->nullable();
            $table->dateTime('fecha_demorado')->nullable();
            $table->dateTime('fecha_terminado')->nullable();
            $table->tinyInteger('estado')->default(1)->comment('1 => Open, 2 => InProcess, 3 => Done, 4 => Delayed');
            $table->string('id_transaccion', 50)->nullable();
            $table->string('pos_ip', 50)->nullable();
            $table->float('tiempo_resolver')->nullable();
            $table->float('porciento_alerta_estado')->nullable();
            $table->unsignedBigInteger('ubicacion_id')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->timestamps();

            $table->foreign('empleado_id')->references('id')->on('tb_empleados');
            $table->foreign('sucursal_id')->references('id')->on('tb_sucursales');
            $table->foreign('terminal_id')->references('id')->on('tb_terminales');
            $table->foreign('ubicacion_id')->references('id')->on('tb_ubicaciones_vk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_tickets_vk');
    }
};
