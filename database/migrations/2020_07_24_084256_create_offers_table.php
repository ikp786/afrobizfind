<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('company_id')->unsigned()->index();
            // $table->string('offer_id',50);
            $table->string('offer_code',50);
            $table->string('offer_details',50); 
            $table->string('discount',50); 
            $table->date('start_date'); 
            $table->date('end_date'); 
            $table->string('customer_only',50);
            $table->string('mobile_number',50);
            $table->char('active',1); 
            $table->softDeletes();
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
        Schema::dropIfExists('offers');
    }
}



 
