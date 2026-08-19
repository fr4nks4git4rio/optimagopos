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
        Schema::create('tb_tickets_vk_items', function (Blueprint $table) {

            $table->id();
            $table->string('nombre');
            $table->float('cantidad');
            $table->string('asiento', 50)->nullable();
            $table->unsignedBigInteger('ticket_vk_id');
            $table->timestamps();

            $table->foreign('ticket_vk_id')->references('id')->on('tb_tickets_vk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_tickets_vk_items');
    }
};
