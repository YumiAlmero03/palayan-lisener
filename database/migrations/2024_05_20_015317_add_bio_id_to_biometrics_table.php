<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBioIdToBiometricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('biometrics', function (Blueprint $table) {
            $table->integer('bio_id')->comment('ref: biometrics.id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('biometrics', function (Blueprint $table) {
            $table->dropColumn('bio_id');
            //
        });
    }
}
