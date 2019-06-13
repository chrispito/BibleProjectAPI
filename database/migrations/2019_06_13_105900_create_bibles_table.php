<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBiblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bibles', function (Blueprint $table) {
            $table->integer('id')->length(8)->unsigned();
            $table->primary('id');
            $table->integer('book_nr')->length(11);
            $table->integer('chapter_nr')->length(11);
            $table->integer('verse_nr')->length(11);
            $table->text('verse');
            $table->integer('type_id')->unsigned();
            $table->foreign('type_id')->references('id')->on('bible_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bibles');
    }
}
