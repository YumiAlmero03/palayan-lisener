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
        $i = 1;
        while (true) {
            try {
                $this->process();
                if ($i === 2) {
                    break;
                }
                $i++;
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                $this->logger->error($e->getMessage());
            }
            sleep($this->sleep);
        }
        echo "Exiting...............\n";
    }

    private function process() {
        echo "1";
        if (!$this->checkDatabaseConnection()) return;
        echo "2";
        // if (!$this->checkInternetConnection()) return;
        echo "3";

        // if ($this->got_disconnected) {
        //     $this->fetchInitialConfiguration();
        //     $this->got_disconnected = false;
        // }

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
        $res = $this->dbh->get_all_logs($this->date, $this->time);
        echo "RECORD COUNT... " . count($res) . " \n";
        $counter = 0;

        foreach ($res as $r) {
            $this->processRecord($r, $counter);
            $counter++;
        }
    }

   private function processRecord($r, $counter) {
        $stud_no = isset($r['userX']) ? $r['userX'] : '';
        $minfo = $this->dbh->send_logs($stud_no, $r['DateX'], $r['TimeX'], $r['datetimeX'], $r['bio_ip'], $r['typeX']);
        $minfo = "";
        $minfo .= ": ID: $stud_no DATE:  Sending data to server!\n";
        $this->dbh->update_logs($r['checkdatetime'], $stud_no, $r['bio_id']);
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
