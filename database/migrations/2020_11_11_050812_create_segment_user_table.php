<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSegmentUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segment_user', function (Blueprint $table) {
            $table->bigInteger('segment_id')->unsigned()->nullable();
            $table->foreign('segment_id')->references('id')->on('segments')->onDelete('cascade');

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment_user', function (Blueprint $table) {
            $table->dropForeign('segment_user_segment_id_foreign');
            $table->dropForeign('segment_user_user_id_foreign');
        });

        Schema::dropIfExists('segment_user');
    }
}
