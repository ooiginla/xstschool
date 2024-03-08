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
        Schema::create('provider_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('transaction_id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('provider_id');
            $table->string('provider_ref');
            $table->string('standard_request')->nullable();
            $table->string('standard_response')->nullable();
            $table->string('provider_request')->nullable();
            $table->string('provider_response')->nullable();
            $table->string('status')->default('PENDING');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_transactions');
    }
};
