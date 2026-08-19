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
        Schema::create('tb_ticket_impuestos', function (Blueprint $table) {

            $table->id();
            $table->float('nombre')->nullable();
            $table->string('monto', 50)->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tb_tickets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_ticket_impuestos');
    }
};
