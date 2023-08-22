<?php

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Biometrics;
use GuzzleHttp\Client;

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
    public function attendanceSendToServer($request)
    {
        
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
