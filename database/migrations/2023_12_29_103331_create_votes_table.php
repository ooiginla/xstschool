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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->nullable();
            $table->string('browser')->nullable();
            $table->string('user_id')->unique();
            $table->string('code')->unique();
            $table->string('chairman')->nullable();
            $table->string('vicechairman')->nullable();
            $table->string('secretary')->nullable();
            $table->string('ass_secretary')->nullable();
            $table->string('treasurer')->nullable();
            $table->string('finsec')->nullable();
            $table->string('pro')->nullable();
            $table->string('legal')->nullable();
            $table->string('welfare')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
