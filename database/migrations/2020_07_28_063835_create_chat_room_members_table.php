<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatRoomMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_room_members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_room_id')->unsigned()->nullable();
            $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->datetime('joined_at')->nullable();
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
        Schema::table('chat_room_members', function (Blueprint $table) {
            $table->dropForeign('chat_room_members_chat_room_id_foreign');
            $table->dropForeign('chat_room_members_user_id_foreign');
        });

        Schema::dropIfExists('chat_room_members');
    }
}
