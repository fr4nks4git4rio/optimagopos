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
        Schema::create('tb_ticket_productos', function (Blueprint $table) {

            $table->id();
            $table->float('precio');
            $table->float('cantidad')->default(0);
            $table->float('descuento')->default(0);
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('departamento_id');
            $table->timestamps();

            $table->foreign('departamento_id')->references('id')->on('tb_departamentos');
            $table->foreign('producto_id')->references('id')->on('tb_productos');
            $table->foreign('ticket_id')->references('id')->on('tb_tickets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ticket_productos');
    }
};
