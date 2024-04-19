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
        Schema::create('business_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('provider_id');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('public_api_key')->nullable();
            $table->string('private_api_key')->nullable();
            $table->string('callback_url')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('allow_auto')->default(true);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_integrations');
    }
};
