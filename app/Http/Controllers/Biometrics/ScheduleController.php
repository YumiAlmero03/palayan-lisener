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
        // try {
        //     $client = new Client();
        //     $res = $client->request('GET', getServerUrl($url).'api/biometrics/getBiometrics');
        //     $biometric = json_decode($res->getBody()->getContents());
        //     // dd($biometric);
        //     foreach ($biometric as $value) {
        //         Biometric::testBiometric($value->bio_ip,$value->bio_proxy);
        //         $bio = Biometric::updateOrCreate(
        //             [
        //                 'id' => $value->id
        //             ],
        //             [
        //                 'bio_ip' => $value->bio_ip,
        //                 'bio_proxy' => $value->bio_proxy,
        //                 'bio_desc' => $value->bio_desc,
        //                 'bio_model' => $value->bio_model,
        //                 'bio_code' => $value->bio_code,
        //                 'bio_department' => $value->bio_department,
        //                 'bio_server' => $url,
        //                 'is_active' => $value->is_active,
        //             ]
        //         );
        //     }
        //     sendLogs('Controller->Biometric->getBiometric','getBiometric done','info','SchedulerLogs');

            
        //     return 'getBiometric done';
        // } catch (\Throwable $th) {
        //     sendLogs('Controller->Biometric->getBiometric',$th,'error','SchedulerLogs');
        //     return 'getBiometric error';
        //     //throw $th;
        // }
        
        try {
            $client = new Client();
            $res = $client->request('GET', getServerUrl($url).'api/biometrics/getBiometrics');
            $biometric = json_decode($res->getBody()->getContents());
            // dd($biometric);
            foreach ($biometric as $value) {
                Biometric::testBiometric($value->bio_ip,$value->bio_proxy);
                $bio = Biometric::updateOrCreate(
                    [
                        'id' => $value->id
                    ],
                    [
                        'bio_ip' => $value->bio_ip,
                        'bio_proxy' => $value->bio_proxy,
                        'bio_desc' => $value->bio_desc,
                        'bio_model' => $value->bio_model,
                        'bio_code' => $value->bio_code,
                        'bio_department' => $value->bio_department,
                        'bio_server' => $url,
                        'is_active' => $value->is_active,
                    ]
                );
                
                $confirm = $client->request('POST', getServerUrl($url).'api/biometrics/confirmBiometric',[
                    'form_params' => [
                        'id' => $value->id,
                        'password' => generateHashApi(),
                    ]
                ]);
            }
            sendLogs('Controller->Biometric->getBiometric','getBiometric done','info','SchedulerLogs');

            return 'getBiometric done';
        } catch (\Throwable $th) {
            sendLogs('Controller->Biometric->getBiometric',$th,'error','SchedulerLogs');
            return 'getBiometric error';
            //throw $th;
        }
    }

    public function getAttendance()
    {
        // try {
        //     $biometrics = Biometric::where('is_active',1)->get();
        //     foreach ($biometrics as $key => $value) {
        //         $value->getAttendance();
        //     }
        //     sendLogs('Controller->Biometric->getAttendance','getAttendance done','info','SchedulerLogs');
        //     return 'getAttendance done';
        // } catch (\Throwable $th) {
        //     sendLogs('Controller->Biometric->getAttendance',$th,'error','SchedulerLogs');
        //     return 'getAttendance error';
        // }
        
        try {
            $biometrics = Biometric::where('is_active',1)->get();
            foreach ($biometrics as $key => $value) {
                $value->getAttendance();
            }
            sendLogs('Controller->Biometric->getAttendance','getAttendance done','info','SchedulerLogs');
            return 'getAttendance done';
        } catch (\Throwable $th) {
            sendLogs('Controller->Biometric->getAttendance',$th,'error','SchedulerLogs');
            return 'getAttendance error';
        }
    }

    public function sendAttendance($url = 'dev')
    {
        // try {
        //     $attendace = BioAttendance::where('hrba_copy',0)->get();
        //     foreach ($attendace as $value) {
        //         $pass = generateHashApi();
        //         $client = new Client();
        //         $res = $client->request('POST', getServerUrl($url).'api/biometrics/recieveAttendance',[
        //             'form_params' => [
        //                 'user_id' => $value->bio_uuid,
        //                 'date' => $value->hrba_date,
        //                 'time' => $value->hrba_time,
        //                 'password' => generateHashApi(),
        //             ]
        //         ]);
        //         $status = $res->getBody()->getContents();
        //         $apiMsg = json_decode($status);
        //         if ($apiMsg->status === 200) {
        //             $value->update(['hrba_copy'=>1]);
        //         } else {
        //             sendLogs('Controller->Biometric->sendAttendance',$status,'error','SchedulerLogs');
        //         }
        //     }

        //     sendLogs('Controller->Biometric->sendAttendance','sendAttendance done','info','SchedulerLogs');
        //     return 'sendAttendance done';
        // } catch (\Throwable $th) {
        //     sendLogs('Controller->Biometric->sendAttendance',$th,'error','SchedulerLogs');
        //     return 'sendAttendance error';
        // }

        try {
            $attendace = BioAttendance::where('hrba_copy',0)->get();
            foreach ($attendace as $value) {
                $pass = generateHashApi();
                $client = new Client();
                $res = $client->request('POST', getServerUrl($url).'api/biometrics/recieveAttendance',[
                    'form_params' => [
                        'user_id' => $value->bio_uuid,
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
                    sendLogs('Controller->Biometric->sendAttendance',$status,'error','SchedulerLogs');
                }
            }

            sendLogs('Controller->Biometric->sendAttendance','sendAttendance done','info','SchedulerLogs');
            return 'sendAttendance done';
        } catch (\Throwable $th) {
            sendLogs('Controller->Biometric->sendAttendance',$th,'error','SchedulerLogs');
            return 'sendAttendance error';
        }
    
    }
    public function test($url = 'local')
    {
        $test = Biometric::testBiometric('192.168.69.98',4370);
        dd($test);
        return 'Test';
    }
}
