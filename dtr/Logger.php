<?php
class Logger
{
    private $handle;
    private $level = 0;
    private $file;
    private $date;
    const DEBUG = 0;
    const INFO = 1; 
    const ERROR = 2;

    public function __construct($config){
        $this->level = 1;
        $this->file = $this->filename();
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                die('Failed to create directory.');
            }
        }
        $this->handle = fopen($this->file, "a");
        if (!$this->handle) {
            die('Failed to open log file.');
        }
        $this->date = date('mdy');
    }

    private function reinit(){
        if ($this->date !== date('mdy')) {
            $this->file = $this->filename();
            $this->handle = fopen($this->file, "a");
            if (!$this->handle) {
                die('Failed to open log file.');
            }
            $this->date = date('mdy');
        }
    }

    private function filename(){ 
        return 'logs/log-'.date('mdy').'.txt'; 
    }

    private function rotate(){
        if ($this->date === date('mdy')) {
            if (is_file($this->file . '1')) {
                unlink($this->file . '1');    
            }
        }
    }

    private function write($m){
        $this->reinit();
        if ($this->handle) {
            $m = date('m-d-y h:i:sA :') . $m . "\r\n";
            if (fwrite($this->handle, $m) === FALSE) {
                echo 'Failed to write to log file.';
            }
        }
    } 

    public function debug($m){
        if ($this->level <= self::DEBUG) $this->write("DEBUG: ".$m);
    }
    
    public function info($m){
        if ($this->level <= self::INFO) $this->write("INFO: ".$m);
    }

    public function error($m){
        if ($this->level <= self::ERROR) $this->write("ERROR: ".$m);
    }
}
?>
