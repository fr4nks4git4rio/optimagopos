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
        Schema::create('tb_tickets', function (Blueprint $table) {

            $table->id();
            $table->string('ubicacion')->nullable();
            $table->string('id_transaccion');
            $table->dateTime('fecha_transaccion')->nullable();
            $table->decimal('importe', 10, 2)->nullable();
            $table->date('vigencia_facturacion')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->unsignedBigInteger('factura_id')->nullable();
            $table->unsignedBigInteger('comensal_id')->nullable();
            $table->timestamps();

            $table->foreign('comensal_id')->references('id')->on('tb_clientes');
            $table->foreign('empleado_id')->references('id')->on('tb_empleados');
            $table->foreign('factura_id')->references('id')->on('tb_facturas');
            $table->foreign('sucursal_id')->references('id')->on('tb_sucursales');
            $table->foreign('terminal_id')->references('id')->on('tb_terminales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_tickets');
    }
};
