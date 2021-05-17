<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactSegmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_segment', function (Blueprint $table) {
            $table->bigInteger('contact_id')->unsigned()->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->bigInteger('segment_id')->unsigned()->nullable();
            $table->foreign('segment_id')->references('id')->on('segments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_segment', function (Blueprint $table) {
            $table->dropForeign('contact_segment_contact_id_foreign');
            $table->dropForeign('contact_segment_segment_id_foreign');
        });

        Schema::dropIfExists('contact_segment');
    }
}
