<?php

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Biometric;
use App\Models\BioAttendance;
use GuzzleHttp\Client;

class ScheduleController extends Controller
{
    /**
     * Get Biometric every hour
     *
     * 
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function getBiometric($url = 'dev')
    {
        // fetch biometrics list and updated from webapp 
        try {
            $client = new Client();
            $res = $client->request('GET', getServerUrl($url).'api/biometrics/getBiometrics');
            $biometrics = json_decode($res->getBody()->getContents());
            $res->getBody()->close();
            $local_server_ip = getHostByName(php_uname('n'));
            foreach ($biometrics as $biometric) {
                if (getNetwork($local_server_ip) == getNetwork($biometric->bio_ip)) {
                    // Biometric::testBiometric($biometric->bio_ip,$biometric->bio_proxy);
                    $bio = Biometric::updateOrCreate(
                        [
                            'bio_id' => $biometric->id
                        ],
                        [
                            'bio_ip' => $biometric->bio_ip,
                            'bio_proxy' => $biometric->bio_proxy,
                            'bio_desc' => $biometric->bio_desc,
                            'bio_model' => $biometric->bio_model,
                            'bio_code' => $biometric->bio_code,
                            'bio_department' => $biometric->bio_department,
                            'bio_server' => $url,
                            'is_active' => $biometric->is_active,
                        ]
                    );
                    
                    $confirm = $client->request('POST', getServerUrl($url).'api/biometrics/confirmBiometric',[
                        'form_params' => [
                            'id' => $biometric->id,
                            'password' => generateHashApi(),
                        ]
                    ]);
                    sendLogs('Controller->Biometric->getBiometric','getBiometric ip:'.$biometric->bio_ip,'info','SchedulerLogs');
                }
            }
            sendLogs('Controller->Biometric->getBiometric','getBiometric done','info','SchedulerLogs');

            return 'getBiometric done';
        } catch (\Throwable $th) {
            sendLogs('Controller->Biometric->getBiometric',$th,'error','throwLogs');
            return 'getBiometric error';
            //throw $th;
        }
    }

    public function getAttendance()
    {
        // fetch attendance from device
        try {
            $biometrics = Biometric::where('is_active',1)->get();
            foreach ($biometrics as $key => $value) {
                $value->getAttendance();
            }
            sendLogs('Controller->Biometric->getAttendance','getAttendance done','info','SchedulerLogs');
            return 'getAttendance done';
        } catch (\Throwable $th) {
            sendLogs('Controller->Biometric->getAttendance',$th,'error','throwLogs');
            return 'getAttendance error';
        }
    }

    public function sendAttendance($url = 'dev')
    {
        // send attendance to web app
        try {
            $attendace = BioAttendance::where('hrba_copy',0)->get();
            foreach ($attendace as $value) {
                $pass = generateHashApi();
                // $client = new Client();
                // $res = $client->request('POST', getServerUrl($url).'api/biometrics/recieveAttendance',[
                //     'form_params' => [
                //         'user_id' => $value->bio_uuid,
                //         'bio_ip_add' => ($value->biometric ? $value->biometric->bio_ip : ''),
                //         'bio_server' => env('APP_ENV'),
                //         'date' => $value->hrba_date,
                //         'time' => $value->hrba_time,
                //         'password' => generateHashApi(),
                //     ]
                // ]);
                // $status = $res->getBody()->getContents();
                // $apiMsg = json_decode($status);
                // if ($apiMsg->status === 200) {
                //     $value->update(['hrba_copy'=>1]);
                // } else {
                //     sendLogs('Controller->Biometric->sendAttendance',$status,'error','SchedulerLogs');
                // }

                // dd(serverStage($url));
                foreach (serverStage($url) as $server) {
                    $client = new Client();
                    $res = $client->request('POST', $server.'api/biometrics/recieveAttendance',[
                        'form_params' => [
                            'user_id' => $value->bio_uuid,
                            'bio_ip_add' => ($value->biometric ? $value->biometric->bio_ip : ''),
                            'bio_server' => env('APP_ENV'),
                            'date' => $value->hrba_date,
                            'time' => $value->hrba_time,
                            'password' => generateHashApi(),
                        ]
                    ]);
                    $status = $res->getBody()->getContents();
                    $apiMsg = json_decode($status);
                    if ($apiMsg->status === 200) {
                        $value->update(['hrba_copy'=>1]);
                    } else {
                        sendLogs('Controller->Biometric->sendAttendance',$status,'info','SchedulerLogs');
                    }
                }
            }

            sendLogs('Controller->Biometric->sendAttendance','sendAttendance done','info','SchedulerLogs');
            return 'sendAttendance done';
        } catch (\Throwable $th) {
            sendLogs('Controller->Biometric->sendAttendance',$th,'error','throwLogs');
            return 'sendAttendance error';
        }
    
    }
    

}
