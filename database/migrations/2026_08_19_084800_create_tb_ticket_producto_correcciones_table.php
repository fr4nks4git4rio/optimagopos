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
        Schema::create('tb_ticket_producto_correcciones', function (Blueprint $table) {

            $table->id();
            $table->string('nombre', 50)->nullable();
            $table->float('cantidad')->nullable();
            $table->float('precio')->nullable();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('tb_productos');
            $table->foreign('ticket_id')->references('id')->on('tb_tickets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ticket_producto_correcciones');
    }
};
