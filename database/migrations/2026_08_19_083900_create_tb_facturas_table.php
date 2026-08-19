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
        Schema::create('tb_facturas', function (Blueprint $table) {

            $table->id();
            $table->string('folio_interno')->nullable();
            $table->string('lugar_expedicion')->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamp('fecha_emision')->nullable();
            $table->timestamp('fecha_certificacion')->nullable();
            $table->text('comentarios')->nullable();
            $table->string('cantidad_letras')->nullable();
            $table->enum('estado', ['PRECAPTURADA', 'CAPTURADA', 'TIMBRADA', 'CANCELADA', 'COBRADA']);
            $table->string('moneda', 20)->nullable();
            $table->string('direccion_xml')->nullable();
            $table->string('direccion_codigo_qr')->nullable();
            $table->text('uuid')->nullable();
            $table->text('cadena_original')->nullable();
            $table->string('numero_serie_sat')->nullable();
            $table->string('numero_serie_emisor')->nullable();
            $table->text('serie_certificado')->nullable();
            $table->text('sello_digital_sat')->nullable();
            $table->text('sello_digital_cfdi')->nullable();
            $table->boolean('modo_prueba_cfdi')->nullable();
            $table->string('cert_rfc_proveedor')->nullable();
            $table->float('porciento_iva')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('iva', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->integer('anio')->nullable();
            $table->string('version_cfdi_timbrado', 10)->default('3.1');
            $table->decimal('tipo_cambio', 10, 4)->nullable();
            $table->text('cfdis_relacionados')->nullable();
            $table->boolean('es_complemento')->default(false);
            $table->boolean('es_nota_credito')->default(false);
            $table->boolean('del_sistema')->default(false);
            $table->string('propietario_type', 50)->default('App\\Models\\Sucursal');
            $table->unsignedBigInteger('cfdi_id')->nullable();
            $table->unsignedBigInteger('metodo_pago_id')->nullable();
            $table->unsignedBigInteger('forma_pago_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('serie_id')->nullable();
            $table->unsignedBigInteger('tipo_comprobante_id')->nullable();
            $table->unsignedBigInteger('propietario_id');
            $table->unsignedBigInteger('tipo_relacion_factura_id')->nullable();
            $table->unsignedBigInteger('motivo_cancelacion_id')->nullable();
            $table->unsignedBigInteger('periodicidad_id')->nullable();
            $table->unsignedBigInteger('mes_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('suscripcion_id')->nullable();
            $table->timestamps();

            $table->foreign('cfdi_id')->references('id')->on('tb_cfdis');
            $table->foreign('cliente_id')->references('id')->on('tb_clientes');
            $table->foreign('metodo_pago_id')->references('id')->on('tb_metodo_pagos');
            $table->foreign('forma_pago_id')->references('id')->on('tb_forma_pagos');
            $table->foreign('serie_id')->references('id')->on('tb_series');
            $table->foreign('tipo_comprobante_id')->references('id')->on('tb_tipo_comprobantes');
            $table->foreign('tipo_relacion_factura_id')->references('id')->on('tb_tipo_relacion_facturas');
            $table->foreign('motivo_cancelacion_id')->references('id')->on('tb_motivos_cancelacion_factura');
            $table->foreign('periodicidad_id')->references('id')->on('tb_periodicidades_factura');
            $table->foreign('mes_id')->references('id')->on('tb_meses');
            $table->foreign('user_id')->references('id')->on('tb_usuarios');
            $table->foreign('suscripcion_id')->references('id')->on('tb_suscripciones');
            // NOTE: `propietario_id` is used polymorphically (see propietario_type) so no FK constraint was defined in the source schema.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_facturas');
    }
};
