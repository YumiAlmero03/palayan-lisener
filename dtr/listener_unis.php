<?php
require_once('Logger.php');
require_once('config.php');
require_once('dbaccess_unis.php');
require_once('JSON.php');
ini_set('memory_limit', '-1');

date_default_timezone_set('Asia/Manila');
$log_config = '';

class DTRProcessor {
    private $json;
    private $dbh;
    private $logger;
    private $got_disconnected;
    private $sleep;
    private $time;
    private $date;
    private $cdate;

    public function __construct($db, $user, $pass, $log_config, $sleep = 1) {
        $this->json = new Services_JSON();
        $this->dbh = new DBAccess($db, $user, $pass);
        $this->logger = new Logger($log_config);
        $this->got_disconnected = true;
        $this->sleep = $sleep;
    }

    public function start() {
        echo "Started....\n";
        while (true) {
            try {
                $this->process();
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                $this->logger->error($e->getMessage());
            }
            sleep($this->sleep);
        }
        echo "Exiting...............\n";
    }

    private function process() {
        if (!$this->checkDatabaseConnection()) return;
        if (!$this->checkInternetConnection()) return;

        if ($this->got_disconnected) {
            $this->fetchInitialConfiguration();
            $this->got_disconnected = false;
        }

        $this->fetchAndProcessRecords();
        $this->updateConfigurationIfDateChanged();
    }

    private function checkDatabaseConnection() {
        if (!$this->dbh->is_connected()) {
            echo "Connecting to DTR DB...\n";
            $this->dbh->disconnect();
            $this->dbh->connect();
            sleep($this->sleep);
            return false;
        }
        return true;
    }

    private function checkInternetConnection() {
        if (!$this->dbh->has_internet()) {
            echo "Cannot access SMS Server, will retry in 5 seconds...\n";
            $this->got_disconnected = true;
            sleep(5);
            return false;
        }
        return true;
    }

    private function fetchInitialConfiguration() {
        $config = $this->json->decode($this->dbh->get_config());
        $this->time = $config->time;
        $this->date = $config->date;
        $n1date = date('Ymd');
        $this->cdate = ($n1date != $this->date) ? $n1date : $this->date;
        echo "Getting last record.. ($this->date) ..\n";
    }

    private function fetchAndProcessRecords() {
        echo "Fetching New Record.... \n";
        $res = $this->dbh->get_user_logs($this->date, $this->time);
        echo "RECORD COUNT... " . count($res) . " \n";
        $counter = 0;

        foreach ($res as $r) {
            $this->processRecord($r, $counter);
            $counter++;
        }
    }

   private function processRecord($r, $counter) {
        $stud_no = isset($r['userX']) ? $r['userX'] : '';
        // $datetime = date('m-d-y h:i:sA', strtotime($r['DateX'] . $r['TimeX']));
        // $minfo = $this->dbh->send_sms($stud_no, $r['C_Date'], $r['C_Time'], $r['L_Mode'], $r['L_TID']);
        
        $minfo = $this->dbh->send_logs($stud_no, $r['DateX'], $r['TimeX'], $r['datetimeX'], $r['bio_ip'], $r['typeX']);
        // {$datetime}
        $minfo = "";
        $minfo .= ": ID: $stud_no DATE:  Sending data to server!\n";
        echo $minfo;
        // $this->logger->info($minfo);
        // $this->time = $r['DateX'];
        // $this->date = $r['TimeX']; 
        $this->dbh->update_logs($r['checkdatetime'], $stud_no, $r['bio_id']);

        // $this->dbh->update_record($this->date, $this->time, $stud_no);
        // echo "..\nDate Now: " . $this->date . " - Time: " . $this->time . " -  ID No: " . $stud_no . " - Record Count: " . $counter . "..\n";
    }
    private function updateConfigurationIfDateChanged() {
        $ndate = date('Ymd');
        echo "Date Now: " . $ndate . " - Date Config: " . $this->cdate . " - Time: " . $this->time . "..\n";
        if ($ndate != $this->cdate) {
            echo "Date has been Changed...\n";
            $this->fetchInitialConfiguration();
        }
    }
}

$processor = new DTRProcessor($db, $user, $pass, $log_config, isset($sleep) ? $sleep : 1);
$processor->start();
?>
