<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->char('customer_number',9);
            $table->string('first_name',50);
            $table->string('surname',50);
            $table->string('email')->unique();
            $table->string('username',50)->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('home_number',50)->nullable();
            $table->string('address_line_1',150)->nullable();
            $table->string('city',50)->nullable();
            $table->string('postcode',50)->nullable();
            $table->string('mobile_number',50)->nullable();
            $table->string('user_number')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}



