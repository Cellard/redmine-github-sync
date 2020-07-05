<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetOwnerIdRequiredInMirrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mirrors', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->change();
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
            $table->unsignedBigInteger('owner_id')->nullable()->change();
        });
    }
}
