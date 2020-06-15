<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMilestoneUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('milestone_user', function (Blueprint $table) {
            $table->unsignedBigInteger('milestone_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('milestone_id')->references('id')->on('milestones');
            $table->foreign('user_id')->references('id')->on('users');

            $table->primary(['milestone_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('milestone_user');
    }
}
