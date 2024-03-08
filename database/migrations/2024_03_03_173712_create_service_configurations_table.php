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
        Schema::create('service_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('service_id');
            $table->string('default_provider');
            $table->string('subscribed_providers')->nullable();
            $table->string('excluded_providers')->nullable();
            $table->string('routing_rule')->nullable();;
            $table->integer('routing_value')->nullable();;
            $table->float('custom_fee')->nullable();
            $table->float('custom_commission')->nullable();;
            $table->float('custom_cap_amount')->nullable();;
            $table->boolean('queue_enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_configurations');
    }
};
