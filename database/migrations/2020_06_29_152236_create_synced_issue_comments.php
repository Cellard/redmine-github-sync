<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncedIssueComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('synced_issue_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issue_comment_id');
            $table->unsignedBigInteger('project_id');
            $table->foreign('issue_comment_id')->references('id')->on('issue_comments');
            $table->foreign('project_id')->references('id')->on('projects');
            $table->integer('ext_id');
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
        Schema::dropIfExists('synced_issue_comments');
    }
}
