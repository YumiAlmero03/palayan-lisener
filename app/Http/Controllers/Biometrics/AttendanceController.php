<?php

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Biometric;

class AttendanceController extends Controller
{
    public function index($ip)
    {
        $test = Biometric::listUser($ip,4370);
        dd($test);
        return 'Test';
    }
}
