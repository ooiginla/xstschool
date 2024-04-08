<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedbigInteger('business_id');
            $table->unsignedbigInteger('product_id');
            $table->string('business_ref');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('account_name');
            $table->string('account_type');
            $table->decimal('balance', 20, 2)->default(0.00);
            $table->boolean('can_overdraw')->default(false);
            $table->decimal('overdraw_limit', 20, 2)->default(0.00);
            $table->string('currency')->default('NGN');
            $table->string('account_no', 10)->nullable()->unique();
            $table->boolean('sms_subscribe')->default(false);
            $table->boolean('email_subscribe')->default(false);
            // virtual account
            $table->timestamps();

            $table->unique(['business_id', 'product_id', 'business_ref'], 'business_product_ref');
            $table->unique(['business_id', 'product_id', 'account_no'], 'business_product_account_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};
