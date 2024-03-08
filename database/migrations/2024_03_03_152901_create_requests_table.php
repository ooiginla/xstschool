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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('client_ref');
            $table->string('oystr_ref')->unique();
            $table->string('provider_ref')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->string('service_name')->nullable();
            $table->unsignedInteger('service_id')->nullable();
            $table->unsignedInteger('provider_id')->nullable();
            $table->string('narration')->nullable();
            $table->string('currency')->default('NGN');
            $table->string('payment_status'); // paid | pending | failed | reversed | liened
            $table->string('request_status'); // PENDING | SUCCESSFUL | FAILED
            $table->string('provider_status'); // PENDING | SUCCESSFUL | FAILED
            $table->string('response_code')->nullable(); 
            $table->string('response_message')->nullable();
            $table->float('client_price', 20, 4);
            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
