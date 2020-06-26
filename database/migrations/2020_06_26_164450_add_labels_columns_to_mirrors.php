<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelsColumnsToMirrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mirrors', function (Blueprint $table) {
            $table->json('ltr_labels')->after('left_id')->nullable();
            $table->json('rtl_labels')->after('right_id')->nullable();
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
            $table->dropColumn('ltr_labels');
            $table->dropColumn('rtl_labels');
        });
    }
}
