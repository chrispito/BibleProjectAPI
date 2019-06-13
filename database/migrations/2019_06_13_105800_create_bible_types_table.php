<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBibleTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bible_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 100)->nullable();
            $table->string('sub_type', 100)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('sub_name', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bible_types');
    }
}
