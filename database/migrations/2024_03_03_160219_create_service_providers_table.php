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
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('provider_id');
            $table->float('sp_fee');
            $table->float('sp_commission');
            $table->float('sp_cap_amount')->nullable()->default(NULL);
            $table->float('business_fee');
            $table->float('business_commission');
            $table->float('business_cap_amount')->nullable()->default(NULL);
            $table->boolean('status');
            $table->char('code',3);
            $table->string('message');
            $table->datetime('next_retry');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
