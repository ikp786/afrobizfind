<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('table_number')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->tinyInteger('active')->nullable()->default(1);

            $table->foreign('company_id')->references('id')->on('companies');
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
        Schema::dropIfExists('table_services');
    }
}
