<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BioAttendance extends Model
{
    protected $guarded = ['id'];
    public $table = 'hr_bio_attendance';
}
