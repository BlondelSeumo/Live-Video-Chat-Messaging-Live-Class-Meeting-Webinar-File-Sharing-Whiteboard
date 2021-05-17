<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(0);
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->dateTime('completed_at')->nullable();
            
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->json('meta')->nullable();
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
        Schema::table('todos', function (Blueprint $table) {
            $table->dropForeign('todos_user_id_foreign');
        });
        
        Schema::dropIfExists('todos');
    }
}
