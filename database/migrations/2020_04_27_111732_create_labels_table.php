<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('ext_id')->comment('external id');
            $table->string('type')->nullable();
            $table->string('name');
            $table->json('more')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects');

            $table->unique(['project_id', 'ext_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('labels');
    }
}
