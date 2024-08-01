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
    public static function getBiometric($url = 'prod')
    {
        // fetch biometrics list and updated from webapp 
        try {
            $client = new Client();
            $server = getServerUrl($url);
            $res = $client->request('GET', $server->server_url.'api/biometrics/getBiometrics');
            $biometrics = json_decode($res->getBody()->getContents());
            $res->getBody()->close();
            $local_server_ip = getHostByName(php_uname('n'));
            foreach ($biometrics as $biometric) {
                if (getNetwork($local_server_ip) == getNetwork($biometric->bio_ip)) {
                    $bio = new Biometric();
                    $bio->testBiometric($biometric->bio_ip,$biometric->bio_proxy);
                    $bio = $bio->updateOrCreate(
                        [
                            'bio_server' => $server->server_name,
                            'bio_id' => $biometric->id
                        ],
                        [
                            'bio_ip' => $biometric->bio_ip,
                            'bio_proxy' => $biometric->bio_proxy,
                            'bio_desc' => $biometric->bio_desc,
                            'bio_model' => $biometric->bio_model,
                            'bio_code' => $biometric->bio_code,
                            'bio_department' => $biometric->bio_department,
                            'is_active' => $biometric->is_active,
                        ]
                    );
                    
                    $confirm = $client->request('POST', $server->server_url.'api/biometrics/confirmBiometric',[
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
        return 'getBiometric done';
        
    }

    /**
     * Fetches attendance from biometric devices.
     *
     * This function retrieves today's attendance from the active biometrics and calls the `getAttendanceToday` method
     * on each of them. It then logs the success or failure of the attendance retrieval.
     *
     * @throws \Throwable If an error occurs during the attendance retrieval process.
     * @return string Returns 'getAttendance done' on success, 'getAttendance error' on failure.
     */
    public static function getAttendance()
    {
        // fetch attendance from biometric devices
        try {
            $bio = new Biometric();
            // dd($bio->testZteco());
            $biometrics = $bio->where('is_active',1)->get();
            foreach ($biometrics as $key => $value) {
                $value->getAttendanceToday();
            }
            sendLogs('Controller->Biometric->getAttendance','getAttendance done','info','SchedulerLogs');
            return 'getAttendance done';
        } catch (\Throwable $th) {
            sendLogs('Controller->Biometric->getAttendance',$th,'error','throwLogs');
            return 'getAttendance error'.$th;
        }
    }

    /**
     * Sends attendance to the web app.
     *
     * This function retrieves attendance records from the database that have not been copied to the web app,
     * and sends them to the web app using the HTTP POST method. The attendance records are sent to the server
     * specified by the `$url` parameter, which defaults to 'dev' if not provided. The function uses the GuzzleHttp
     * library to make the HTTP request. If the attendance record is successfully sent to the web app, the
     * `hrba_copy` field of the attendance record is updated to 1. If an error occurs during the sending process,
     * the error is logged using the `sendLogs` function.
     *
     * @param string $url The URL of the server to send the attendance records to. Defaults to 'dev'.
     * @throws \Throwable If an error occurs during the sending process.
     * @return string Returns 'sendAttendance done' on success, 'sendAttendance error' on failure.
     */
    public static function sendAttendance($url = 'dev')
    {
        // send attendance to web app
        try {
            $attendace = BioAttendance::where('hrba_copy',0)->limit(100)->get();
            foreach ($attendace as $value) {
                $pass = generateHashApi();
                $client = new Client();
                $res = $client->request('POST', getServerUrl($url)->server_url.'api/biometrics/recieveAttendance',[
                    'form_params' => [
                        'user_id' => $value->bio_uuid,
                        'date' => $value->hrba_date,
                        'time' => $value->hrba_time,
                        'bio_ip_add' => ($value->biometric ? $value->biometric->bio_ip : ''),
                        'bio_server' => env('APP_ENV'),
                        'biometric_id' => $value->hrba_time,
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
                // foreach (serverStage($url) as $server) {
                //     $res = $client->request('POST', $server->server_url.'api/biometrics/recieveAttendance',[
                //         'form_params' => [
                //             'user_id' => $value->bio_uuid,
                //             'bio_ip_add' => ($value->biometric ? $value->biometric->bio_ip : ''),
                //             'bio_server' => env('APP_ENV'),
                //             'date' => $value->hrba_date,
                //             'time' => $value->hrba_time,
                //             'password' => generateHashApi(),
                //         ]
                //     ]);
                //     $status = $res->getBody()->getContents();
                //     $apiMsg = json_decode($status);
                //     if ($apiMsg->status === 200) {
                //         $value->update(['hrba_copy'=>1]);
                //     } else {
                //         sendLogs('Controller->Biometric->sendAttendance server:'.$server->server_name,$status,'error','SchedulerLogs');
                //     }
                // }
            }
            sendLogs('Controller->Biometric->sendAttendance','sendAttendance done','info','SchedulerLogs');
            return 'sendAttendance done';
        } catch (\Throwable $th) {
            sendLogs('Controller->Biometric->sendAttendance',$th,'error','throwLogs');
            return 'sendAttendance error'.$th;
        }
    
    }
    

}
