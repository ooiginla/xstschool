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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('business_id');
            $table->bigInteger('product_id');
            $table->string('reference')->unique();
            $table->tinyInteger('status');
            $table->decimal('total_sent', 20, 2);
            $table->decimal('total_debit', 20, 2);
            $table->string('message');
            $table->json('payload');
            $table->string('idempotency', 1000);
            $table->string('provider_reference', 100)->nullable();
            $table->string('provider', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};