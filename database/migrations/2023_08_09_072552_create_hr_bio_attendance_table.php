<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrBioAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_bio_attendance', function (Blueprint $table) {
            $table->id();
            $table->integer('biometric_id')->comment('refTable: biometrics.id');
            $table->integer('bio_uid')->comment('ref: uid in biometrics');
            $table->integer('bio_uuid')->comment('ref: user id in biometrics');
            $table->integer('bio_state')->comment('the authentication type, 1 for Fingerprint, 4 for RF Card etc');
            $table->datetime('bio_timestamp');
            $table->integer('bio_type')->comment('attendance type, like check-in, check-out, overtime-in, overtime-out, break-in & break-out etc. if attendance type is none of them, it gives  255');
            $table->date('hrba_date');
            $table->time('hrba_time');
            $table->integer('hrba_copy')->default(0);
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
        Schema::dropIfExists('hr_bio_attendance');
    }
}
