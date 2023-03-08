<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->length(60);
            $table->string('otp')->length(6)->nullable();
            $table->string('account_set_code')->length(60)->nullable();
            $table->string('ip_address')->length(20);
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
        Schema::dropIfExists('users_otps');
    }
}
