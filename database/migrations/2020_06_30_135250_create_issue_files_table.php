<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssueFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issue_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->text('description')->nullable();
            $table->integer('ext_id');
            $table->unsignedBigInteger('issue_id');
            $table->unsignedBigInteger('author_id');
            $table->foreign('issue_id')->references('id')->on('issues');
            $table->foreign('author_id')->references('id')->on('users');
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
        Schema::dropIfExists('issue_files');
    }
}
