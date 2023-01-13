<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_payment_methods', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('user_id')->unsigned();

            $table->integer('last_four_digits')->nullable();
            $table->boolean('is_attached')->nullable();
            $table->json('payment_method_responce')->nullable();
            $table->json('customer_attach_responce')->nullable();
            $table->string('payment_method_id')->nullable();
            $table->string('country')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('type')->nullable();
            $table->string('code')->nullable();
            $table->string('expiry_month')->nullable();
            $table->string('expiry_year')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripe_payment_methods');
    }
};
