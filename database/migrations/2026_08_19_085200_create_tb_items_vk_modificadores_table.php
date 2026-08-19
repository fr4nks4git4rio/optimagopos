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
        Schema::create('tb_items_vk_modificadores', function (Blueprint $table) {

            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('modificador_id');
            $table->primary(['item_id', 'modificador_id']);

            $table->foreign('modificador_id')->references('id')->on('tb_modificadores_vk');
            $table->foreign('item_id')->references('id')->on('tb_tickets_vk_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_items_vk_modificadores');
    }
};
