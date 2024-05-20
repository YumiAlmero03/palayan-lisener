<?php
use App\Models\Server;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

function serverStage($server)
{
    $stage = config('constants.server')[$server];
    $list = [];
    foreach ($stage as $value) {
        if (getServerUrl($value)) {
            $list[] = getServerUrl($value);
        }
    }
    return $list;
}
function getServerUrl($server)
{
    $server = Server::where([['server_name',$server],['server_status',1]])->first();
    return $server;
}

function sendLogs($loc,$msg,$type = 'info',$file = 'throwLogs'){
    $log = Log::build([
        'driver' => 'single',
        'path' => storage_path().'/logs/'.$file.'-'.Carbon::now()->year.'.log',
    ]);
    $msg = $loc . '::' .$msg;
    switch ($type) {
        case 'error':
            $log->error($msg);
            break;
        
        default:
            $log->info($msg);
            break;
    }
}

function generateHashApi() {
    return bcrypt(config('constants.password.server-api'));
}