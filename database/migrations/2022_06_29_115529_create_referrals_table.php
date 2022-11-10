<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_number')->nullable();
            $table->integer('referrer')->nullable();
            $table->date('first_payment')->nullable();
            $table->integer('number_of_payments')->nullable();
            $table->integer('subscription_status')->nullable();
            $table->string('company_name')->nullable();
            $table->string('first_line_address')->nullable();
            $table->integer('postcode')->nullable();
            $table->bigInteger('company_contact_number')->nullable();
            $table->string('customer_number')->nullable();
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
        Schema::dropIfExists('referrals');
    }
}
