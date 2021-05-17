<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('options')->onDelete('cascade');

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
        Schema::table('options', function (Blueprint $table) {
            $table->dropForeign('options_parent_id_foreign');
        });
        
        Schema::dropIfExists('options');
    }
}
