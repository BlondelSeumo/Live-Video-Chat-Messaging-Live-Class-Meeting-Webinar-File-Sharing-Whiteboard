<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_user', function (Blueprint $table) {
            $table->bigInteger('contact_id')->unsigned()->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_user', function (Blueprint $table) {
            $table->dropForeign('contact_user_contact_id_foreign');
            $table->dropForeign('contact_user_user_id_foreign');
        });

        Schema::dropIfExists('contact_user');
    }
}
