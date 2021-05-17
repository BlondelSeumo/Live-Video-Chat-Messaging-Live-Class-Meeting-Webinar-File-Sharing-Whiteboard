<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->text('agenda')->nullable();
            $table->longText('description')->nullable();
            $table->datetime('start_date_time')->nullable();
            $table->integer('period')->default(0);

            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('options')->onDelete('set null');

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

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
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign('meetings_category_id_foreign');
            $table->dropForeign('meetings_user_id_foreign');
        });
        
        Schema::dropIfExists('meetings');
    }
}
