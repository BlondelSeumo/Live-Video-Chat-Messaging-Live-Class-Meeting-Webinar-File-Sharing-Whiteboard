<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingInviteesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_invitees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->boolean('is_attendee')->default(0);

            $table->bigInteger('meeting_id')->unsigned()->nullable();
            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            
            $table->bigInteger('contact_id')->unsigned()->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            
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
        Schema::table('meeting_invitees', function (Blueprint $table) {
            $table->dropForeign('meeting_invitees_meeting_id_foreign');
            $table->dropForeign('meeting_invitees_contact_id_foreign');
        });

        Schema::dropIfExists('meeting_invitees');
    }
}
