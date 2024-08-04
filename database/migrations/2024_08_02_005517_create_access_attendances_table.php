<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_attendances', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('userid')->nullable();
            $table->string('chk_date')->nullable();
            $table->string('chk_time')->nullable();
            $table->string('chk_datetime')->nullable();
            $table->string('bio_ip')->nullable();
            $table->string('type')->nullable();
            $table->string('is_copy')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_attendances');
    }
}
