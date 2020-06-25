<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMirrorLabelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mirror_label', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mirror_id');
            $table->unsignedBigInteger('left_label_id');
            $table->unsignedBigInteger('right_label_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mirror_label');
    }
}
