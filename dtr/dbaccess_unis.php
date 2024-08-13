<?php 
class DBAccess 
{
    private $conn;
    private $db;
    private $u;
    private $p;
    
    public function __construct($db,$user,$pass){
        $this->db = $db;
        $this->u = $user;
        $this->p = $pass;
    }

    public function connect() {
        // Check if the database file path is set
        if (!isset($this->db) || empty($this->db)) {
            throw new Exception("Database file path is not set.");
        }

        // Use the correct driver for Access databases (*.mdb for older versions, *.accdb for newer versions)
        $driver = "Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$this->db";
        // Establish the connection
        $this->conn = odbc_connect($driver, $this->u, $this->p);

        // Check if the connection was successful
        if (!$this->conn) {
            throw new Exception("Connection failed: " . odbc_errormsg());
        }

        echo "Connection successful!";
    }
    public function disconnect(){ @odbc_close($this->conn); }

    public function is_connected(){ return ($this->conn && $this->select_one("select 1")) ? true : false; }

    public function has_internet(){ 
        // Check to see if the local machine is connected to the web 
        // Uses sockets to open a connection to apisonline.com 
        // $url = parse_url(SMS_URL);
        // Use isset() to check if 'port' and 'host' are set in the array
        $port = isset($url['port']) ? $url['port'] : 80;
        $host = isset($url['host']) ? $url['host'] : 'localhost'; 
        
        // Attempt to connect to the host on the specified port
        $connected = @fsockopen("tcp://{$host}", $port, $errno, $errstr, 10);
        
        // Check if the connection was successful
        if ($connected){ 
            fclose($connected); 
            return true;
        }
        
        return false; 
    }
        
    public function select_all($sql) {
        $rs = odbc_exec($this->conn, $sql);
        if (!$rs) {
            // Handle the error
            $error = odbc_errormsg($this->conn);
            die("Error in SQL query: $error");
        }
        $result = array();
        while ($res = odbc_fetch_array($rs)) {
            $result[] = $res;
        }
        return $result;
    }

    public function select_one($sql){
        $rs=odbc_exec($this->conn,$sql);
        return odbc_result($rs,1); 
    }
    public function send_sms($stud_no,$e_date,$e_time,$e_mode=0,$e_tid){
        $url = SMS_URL . '?stud_no=' . urlencode($stud_no) . '&e_date=' . urlencode($e_date) . '&e_time=' . urlencode($e_time) . "&e_mode={$e_mode}" . "&e_tid={$e_tid}" . "&e_type=2";
        echo $url;
        return $this->call_url($url); 
    }
    private function call_url($url){
        $sock = @fopen($url,'r');
        if ($sock){ 
            $str = fread($sock, 4096);
            fclose($sock); 
            return $str;
        }

        $ch = curl_init ($url);
        ob_start();
        curl_exec($ch);
        $str = ob_get_contents();
        ob_end_clean();
        curl_close ($ch);        
        return $str;
    }

    public function send_logs($userid, $e_date, $e_time, $datetime, $bio_ip = 0, $type)
    {
        $url = LOCAL_URL . '?userid=' . urlencode($userid) . '&e_date=' . urlencode($e_date) . '&e_time=' . urlencode($e_time) . "&bio_ip={$bio_ip}" . "&type={$type}" . "&datetime=" . urlencode($datetime) ;
        echo $url."
        /n/r";
        return $this->call_url($url); 
    }
    public function get_user_logs($date = false, $time=false){
        $date = ($date) ? $date : date("Ymd");
        $time = ($time) ? $time : "040000";
		echo 'DATE: '.date('m/d/Y', strtotime($date)).'  \n\r';
		$sql="SELECT emp.Badgenumber AS userX,
            FORMAT(tk.CHECKTIME, 'YYYY-MM-DD') AS DateX,
            FORMAT(tk.CHECKTIME, 'HH:MM:SS') AS TimeX,
            FORMAT(tk.CHECKTIME, 'YYYY-MM-DD HH:MM:SS') AS datetimeX,
            tk.CHECKTYPE AS typeX,
            tk.CHECKTIME AS checkdatetime,
            tK.SENSORID AS bio_id,
            MC.IP AS bio_ip
			FROM (CHECKINOUT AS tK 
                LEFT JOIN Machines AS MC ON MC.MachineNumber = tK.SENSORID) 
                LEFT JOIN USERINFO AS emp ON emp.USERID = tK.USERID
			WHERE tK.is_copy = 0 
			AND tk.CHECKTIME LIKE '%".date('n/j/Y', strtotime($date))."%';";
        return $this->select_all($sql);
    }

    public function get_all_logs($date = false, $time=false){
        $date = ($date) ? $date : date("Ymd");
        $time = ($time) ? $time : "040000";
		echo 'DATE: '.date('m/d/Y', strtotime($date)).'  \n\r';
        $sql2 = "UPDATE CHECKINOUT as tE SET tE.is_copy = 0";
        $res = odbc_exec($this->conn,$sql2);
		$sql="
            SELECT emp.Badgenumber AS userX,
            FORMAT(tk.CHECKTIME, 'YYYY-MM-DD') AS DateX,
            FORMAT(tk.CHECKTIME, 'HH:MM:SS') AS TimeX,
            FORMAT(tk.CHECKTIME, 'YYYY-MM-DD HH:MM:SS') AS datetimeX,
            tk.CHECKTYPE AS typeX,
            tk.CHECKTIME AS checkdatetime,
            tK.SENSORID AS bio_id,
            MC.IP AS bio_ip
			FROM (CHECKINOUT AS tK 
                LEFT JOIN Machines AS MC ON MC.MachineNumber = tK.SENSORID)
                LEFT JOIN USERINFO AS emp ON emp.USERID = tK.USERID
			WHERE tK.is_copy = 0 ";
        return $this->select_all($sql);
    }

    public function update_logs($datetime, $userid, $bioid){
        if (!$this->conn) {
            exit("Connection Failed: " . $this->conn);
        }
        $sql2 = "UPDATE CHECKINOUT as tE SET tE.is_copy = 1
                WHERE tE.CHECKTIME LIKE '%".date('n/j/Y g:i:s A', strtotime($datetime))."%' AND tE.USERID=".$userid." AND tE.SENSORID='".$bioid."';";
        $res = odbc_exec($this->conn,$sql2);
        return $res;
    }

    public function update_record($date=false,$time=false,$u_id=false){
            $d = $date;
            $t = $time;
            $u = $u_id;
			if (!$this->conn) {
				exit("Connection Failed: " . $this->conn);
			}
            $sql2 = "UPDATE tEnter as tE set tE.Sent = True
					WHERE tE.C_Date='".$d."' AND tE.C_Time='".$t."' AND tE.C_Unique='".$u."';";
			
				
            $res = odbc_exec($this->conn,$sql2);
            return $res;
    }

    public function get_config(){
        if ($this->has_internet()) {
            $res = $this->call_url(DTR_URL . '?get_config=true');
        }else{
            $res = "";
        }
        return (empty($res)) ? "" : $res; 
    }

}
?>
