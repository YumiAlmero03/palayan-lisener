<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Rats\Zkteco\Lib\ZKTeco;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Biometric extends Model
{
    protected $guarded = ['id'];
    
    public function getList($request){
        $params = $columns = $totalRecords = $data = array();
        $params = $_REQUEST;
        $q=$request->input('q');
        if(!isset($params['start']) && !isset($params['length'])){
            $params['start']="0";
            $params['length']="10";
        }
        $columns = array( 
            1 =>"bio_ip",
            2 =>"bio_proxy",   
            3 =>"bio_department",   
            4 =>"bio_code",   
            5 =>"bio_model",   
        );
        $sql = $this;
        if(!empty($q) && isset($q)){
            $sql = $sql->where(function ($query) use($q) {
                        $query->where(DB::raw('LOWER(bio_ip)'),'like',"%".strtolower($q)."%");
                        $query->orWhere(DB::raw('LOWER(bio_proxy)'),'like',"%".strtolower($q)."%");
                        $query->orWhere(DB::raw('LOWER(bio_department)'),'like',"%".strtolower($q)."%");
                        $query->orWhere(DB::raw('LOWER(bio_code)'),'like',"%".strtolower($q)."%");
                        $query->orWhere(DB::raw('LOWER(bio_model)'),'like',"%".strtolower($q)."%");
                    });
        }
       /*  #######  Set Order By  ###### */
        if(isset($params['order'][0]['column']))
            $sql->orderBy($columns[$params['order'][0]['column']],$params['order'][0]['dir']);
        else
            $sql->orderBy('id','DESC');
       /*  #######  Get count without limit  ###### */
        $data_cnt=$sql->count();
       /*  #######  Set Offset & Limit  ###### */
        $sql->offset((int)$params['start'])->limit((int)$params['length']);
        
        $data=$sql->get();
        return array("data_cnt"=>$data_cnt,"data"=>$data);
    }

    public function start($ip = null,$proxy = 4370)
    {
        try {
            if ($ip == null) {
                $ip = $this->bio_ip;
                $proxy = $this->bio_proxy;
            }
            $zk = new ZKTeco($ip);
            $zk->connect();
            $zk->disableDevice();
            return $zk;
        } catch (\Throwable $th) {
            sendLogs('Models->Biometric->start',$th,'error');
            return [];
        }
        
    }
    
    /**
     * Add user to biometrics
     *
     * parameters
     * 
     * {
     * 'uid' => int(),
     * 'userid' => int|string ( max length = 9),
     * 'name' => string,
     * 'password' => int(),
     * 'ip' => ip,
     * 'cardno' => int() (max length = 10, Default 0),
     * 'role' => int() (default is 1 for normal user| 14 for super user),
     * }
     *
     **/
    public function addUser($request)
    {
        $role = isset($request['role']) ? $request['role'] : 1;
        $cardno = isset($request['cardno']) ? $request['cardno'] : 0;
        $ip = isset($request['ip']) ? $request['ip'] : 0;
        $zk = Self::start($ip);
        $zk->setUser(
            $request['uid'],
            $request['userid'],
            $request['name'],
            $request['password'],
            $role,
            $cardno);
        return $request['uid'];
    }

    /**
     * Remove user to biometrics
     *
     * parameters
     * 
     * $id = uid 
     *
     **/
    public function removeUser($ip = null)
    {
        $zk = Self::start($ip);
        return $zk->removeUser($id);
    }

    public function listUser($ip = null)
    {
        $zk = Self::start($ip);
        return $zk->getUser();
    }

    public function getAttendance($ip = null)
    {
        try {

            $zk = Self::start($ip);
            $attendance = $zk->getAttendance();
            foreach ($attendance as $key => $value) {
                $timestamp = Carbon::parse($value['timestamp']);
                BioAttendance::firstOrCreate(
                    [
                        'biometric_id' => $this->id,
                        'bio_uid' => (int)$value['uid'],
                        'bio_uuid' => (int)$value['id'],
                        'bio_timestamp' => $value['timestamp'],
                    ],
                    [
                        'bio_state' => $value['state'],
                        'bio_type' => $value['type'],
                        'hrba_date' => $timestamp->toDateString(),
                        'hrba_time' => $timestamp->toTimeString(),
                    ]
                );
            }
            return $attendance;
        } catch (\Throwable $th) {
            sendLogs('Models->Biometric->getAttendance',$th,'error');
            //throw $th;
        }
        
    }

    public function testBiometric($ip = null,$proxy = 4370)
    {
        if ($ip == null) {
            $ip = $this->bio_ip;
            $proxy = $this->bio_proxy;
        }
        $process = new Process(['ping',$ip]);
        $process->run();
        $zk = Self::start($ip,$proxy);
        $zk->testVoice();
        $model = $zk->version(); 
        return [
            'model'=>$model
        ];
    }
}
