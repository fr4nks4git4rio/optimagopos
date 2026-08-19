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
        Schema::create('tb_clientes', function (Blueprint $table) {

            $table->id();
            $table->text('nombre_comercial')->nullable();
            $table->text('razon_social')->nullable();
            $table->string('rfc', 50);
            $table->text('correo')->nullable();
            $table->text('telefono')->nullable();
            $table->text('contacto_nombre')->nullable();
            $table->text('contacto_correo')->nullable();
            $table->text('contacto_telefono')->nullable();
            $table->text('contacto_cargo')->nullable();
            $table->text('logo')->nullable();
            $table->boolean('es_comensal')->default(false);
            $table->boolean('es_cliente')->default(false);
            $table->boolean('es_propietario')->default(false);
            $table->boolean('con_facturacion')->default(false);
            $table->boolean('es_cliente_fiel')->default(false);
            $table->text('comentarios')->nullable();
            $table->text('portal_pac')->nullable();
            $table->string('usuario_integrador_sat', 100)->nullable();
            $table->unsignedBigInteger('regimen_fiscal_id')->nullable();
            $table->unsignedBigInteger('direccion_fiscal_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('direccion_fiscal_id')->references('id')->on('tb_direcciones');
            $table->foreign('regimen_fiscal_id')->references('id')->on('tb_regimen_fiscales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_clientes');
    }
};
