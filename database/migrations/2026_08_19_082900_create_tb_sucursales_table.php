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
        Schema::create('tb_sucursales', function (Blueprint $table) {

            $table->id();
            $table->string('id_sucursal', 50)->nullable();
            $table->text('nombre_comercial')->nullable();
            $table->text('razon_social')->nullable();
            $table->string('rfc', 50)->nullable();
            $table->text('correo')->nullable();
            $table->text('telefono')->nullable();
            $table->text('logo')->nullable();
            $table->enum('tipo_vigencia_ticket_facturacion', ['last_day_month', 'days_number_after_emitted', 'days_number_next_month'])->nullable();
            $table->integer('dias_vigencia')->nullable();
            $table->text('portal_pac')->nullable();
            $table->string('usuario_integrador_sat', 100)->nullable();
            $table->boolean('cfdi_timbrado_productivo')->default(false);
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('direccion_fiscal_id')->nullable();
            $table->unsignedBigInteger('regimen_fiscal_id')->nullable();
            $table->unsignedBigInteger('moneda_base_id')->nullable();
            $table->unsignedBigInteger('moneda_facturacion_id')->nullable();
            $table->unsignedBigInteger('suscripcion_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cliente_id')->references('id')->on('tb_clientes');
            $table->foreign('direccion_fiscal_id')->references('id')->on('tb_direcciones');
            $table->foreign('regimen_fiscal_id')->references('id')->on('tb_regimen_fiscales');
            $table->foreign('moneda_base_id')->references('id')->on('tb_monedas');
            $table->foreign('moneda_facturacion_id')->references('id')->on('tb_monedas');
            $table->foreign('suscripcion_id')->references('id')->on('tb_suscripciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_sucursales');
    }
};
