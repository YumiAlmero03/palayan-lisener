<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessAttendance extends Model
{
    protected $guarded = ['id'];
    public function biometric() 
    { 
        return $this->belongsTo('App\Models\Biometric', 'bio_ip', 'bio_ip'); 
    }
}
