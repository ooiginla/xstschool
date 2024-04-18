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
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('provider_id');
            $table->string('action')->nullable();
            $table->string('own_reference',50)->nullable();
            $table->string('provider_ref')->nullable();
            $table->mediumText('standard_request')->nullable();
            $table->mediumText('standard_response')->nullable();
            $table->mediumText('provider_request')->nullable();
            $table->string('provider_httpcode')->nullable();
            $table->mediumText('provider_response')->nullable();
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
