<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMilestonesToMirrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mirrors', function (Blueprint $table) {
            $table->unsignedBigInteger('left_milestone_id')->nullable()->after('left_id');
            $table->unsignedBigInteger('right_milestone_id')->nullable()->after('right_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mirrors', function (Blueprint $table) {
            $table->dropColumn('left_milestone_id');
            $table->dropColumn('right_milestone_id');
        });
    }
}
