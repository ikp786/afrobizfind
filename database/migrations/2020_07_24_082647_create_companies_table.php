<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->index();
            $table->bigInteger('category_id')->unsigned()->index();
            $table->bigInteger('currency_id');
            $table->string('image')->nullable();
            $table->string('company_name',50);
            $table->string('building_number',50);
            $table->string('address_line_1',100);
            $table->string('city',50);
            $table->string('postcode',50);
            $table->time('monday_opening')->nullable();
            $table->time('monday_closing')->nullable();
            $table->time('tuesday_opening')->nullable();
            $table->time('tuesday_closing')->nullable();
            $table->time('wednesday_opening')->nullable();
            $table->time('wednesday_closing')->nullable();
            $table->time('thursday_opening')->nullable();
            $table->time('thursday_closing')->nullable();
            $table->time('friday_opening')->nullable();
            $table->time('friday_closing')->nullable();
            $table->time('saturday_opening')->nullable();
            $table->time('saturday_closing')->nullable();
            $table->time('sunday_opening')->nullable();
            $table->time('sunday_closing')->nullable();
            $table->string('email',50);
            $table->string('telephone',50);
            //$table->string('company_number',50);
            $table->string('website',50);
            $table->string('lat',20);
            $table->string('long',20);

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
        Schema::dropIfExists('companies');
    }
}
