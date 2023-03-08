<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                    ->constrained('users')
                    ->onDelete('cascade');
            $table->string('task_code')->length(10);
            $table->string('name')->length(100);
            $table->text('description')->nullable();
            $table->text('key_points');
            $table->integer('progress')->length(1)->default(1)->comment('1 => Pending, 2 => In Pregress, 3 => Testing, 4 => Done, 5 => Closed');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
