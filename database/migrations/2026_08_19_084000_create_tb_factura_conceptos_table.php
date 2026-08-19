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
        Schema::create('tb_factura_conceptos', function (Blueprint $table) {

            $table->id();
            $table->float('cantidad');
            $table->decimal('precio_unitario', 10, 2)->default(0.00);
            $table->float('descuento')->default(0);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('factura_id');
            $table->unsignedBigInteger('clave_prod_serv_id')->nullable();
            $table->unsignedBigInteger('clave_unidad_id')->nullable();
            $table->unsignedBigInteger('objeto_impuesto_id')->nullable();
            $table->unsignedBigInteger('suscripcion_id')->nullable();
            $table->timestamps();

            $table->foreign('factura_id')->references('id')->on('tb_facturas');
            $table->foreign('clave_prod_serv_id')->references('id')->on('tb_clave_prod_servs');
            $table->foreign('clave_unidad_id')->references('id')->on('tb_clave_unidades');
            $table->foreign('objeto_impuesto_id')->references('id')->on('tb_objetos_impuesto');
            $table->foreign('suscripcion_id')->references('id')->on('tb_suscripciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_factura_conceptos');
    }
};
