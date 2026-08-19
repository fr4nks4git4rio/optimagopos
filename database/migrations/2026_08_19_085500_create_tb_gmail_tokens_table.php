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
        Schema::create('tb_gmail_tokens', function (Blueprint $table) {

            $table->id();
            $table->string('email')->default('')->unique();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->integer('expires')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_gmail_tokens');
    }
};
