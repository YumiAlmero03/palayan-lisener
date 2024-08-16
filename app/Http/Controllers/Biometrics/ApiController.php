<?php

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Biometrics;
use App\Models\AccessAttendance;
use GuzzleHttp\Client;
use Carbon\Carbon;
class ApiController extends Controller
{
    /**
     * Send to Server
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function lastAttendance()
    {
        $now = Carbon::today();
        return json_encode([
            'time' => $now->toTimeString(),
            'date' => $now->toDateString(),
        ]);
    }
    public function recieveAttendance(Request $request)
    {
        try {
            $record = AccessAttendance::firstOrCreate(
                [
                    'userid' => $request->userid,
                    'chk_date' => $request->e_date,
                    'chk_time' => $request->e_time,
                    'bio_ip' => $request->bio_ip,
                    'chk_datetime' => $request->datetime,
                ],
                [
                    'type' => $request->type,
                ]
            );
            
            return json_encode([
                'status' => 200,
                'type' => 'success',
                'msg' => 'Attendance Sent!'
            ]);
        } catch (\Throwable $th) {
            return json_encode([
                'status' => 500,
                'type' => 'error',
                'msg' => $th
            ]);
        }
            
    }
    
    /**
     * Test Connection to Biometric
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function connection(Request $request)
    {
        $ip = $request->ip;
        $data = Biometrics::testBiometric($ip);
        $success_msg = 'Success';
        return json_encode(
            [
                'ESTATUS'=>0,
                'msg'=>$success_msg,
                'data' => $data
            ]
        );
    }


}
