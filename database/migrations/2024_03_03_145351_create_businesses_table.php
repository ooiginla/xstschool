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
        Schema::create('businesses', function (Blueprint $table) {

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('reg_no')->nullable();
            $table->string('category')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('logo_path')->nullable();
            $table->char('currency', 3)->default('NGN')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->boolean('admin_verified')->default(false);
            $table->boolean('is_data_provider')->default(false);
            $table->boolean('is_data_consumer')->default(true);
            $table->boolean('test_enabled')->default(true);
            $table->boolean('live_enabled')->default(false);
            $table->boolean('status')->default(false);
            $table->string('whitelisted_ips')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('webhook_url')->nullable();
            $table->char('current_environment', 8)->default('test');
            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};