<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('slug')->change();
            $table->integer('order_column')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tags', function(Blueprint $table)
        {
            $table->text('name')->change();
            $table->text('slug')->change();
            $table->dropColumn('order_column');
        });
    }
}