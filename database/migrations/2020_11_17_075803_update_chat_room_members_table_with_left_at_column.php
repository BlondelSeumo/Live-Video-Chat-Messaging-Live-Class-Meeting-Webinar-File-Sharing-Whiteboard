<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateChatRoomMembersTableWithLeftAtColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_room_members', function (Blueprint $table) {
            $table->datetime('left_at')->nullable()->after('joined_at');
            $table->boolean('is_owner')->default(0)->after('left_at');
            $table->boolean('is_moderator')->default(0)->after('is_owner');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_room_members', function(Blueprint $table)
        {
            $table->dropColumn('is_moderator');
            $table->dropColumn('is_owner');
            $table->dropColumn('left_at');
        });
    }
}
