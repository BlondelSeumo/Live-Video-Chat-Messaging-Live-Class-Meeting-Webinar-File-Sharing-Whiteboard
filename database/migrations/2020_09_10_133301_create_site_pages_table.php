<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('body')->nullable();
            $table->boolean('status')->default(0);
            
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('site_pages')->onDelete('set null');
            
            $table->bigInteger('template_id')->unsigned()->nullable();
            $table->foreign('template_id')->references('id')->on('options')->onDelete('set null');
            
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
        Schema::table('site_pages', function (Blueprint $table) {
            $table->dropForeign('site_pages_parent_id_foreign');
            $table->dropForeign('site_pages_template_id_foreign');
        });
        
        Schema::dropIfExists('site_pages');
    }
}
