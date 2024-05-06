<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColBioUuidHrBioAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hr_bio_attendance', function (Blueprint $table) {
            $table->string('bio_uuid', 50)->change();
        });
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hr_bio_attendance', function (Blueprint $table) {
            $table->integer('bio_uuid')->change();
        });
    }
}
