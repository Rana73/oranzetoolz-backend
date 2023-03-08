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
            $table->id();
            $table->string('name')->length(50)->nullable();;
            $table->string('user_code')->length(10);
            $table->string('mobile')->length(11)->nullable();;
            $table->string('email')->length(60)->unique();
            $table->string('password')->length(60);
            $table->string('pass_reset_code')->length(60)->nullable();
            $table->string('ip_address')->length(19);
            $table->text('client_info')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->commonFields();
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
