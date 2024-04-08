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
        Schema::create('retries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('request_id');
            $table->string('provider_code',10)->nullable();
            $table->bigInteger('provider_transaction_id')->nullable();
            $table->string('prev_payment_status');
            $table->string('prev_request_status');
            $table->string('prev_response_code');
            $table->string('prev_response_message');
            $table->datetime('prev_updated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retries');
    }
};
