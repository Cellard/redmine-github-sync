<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMirrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mirrors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->morphs('left');
            $table->json('ltr_labels')->nullable();
            $table->morphs('right');
            $table->json('rtl_labels')->nullable();
            $table->string('config')->default('both');
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mirrors');
    }
}
