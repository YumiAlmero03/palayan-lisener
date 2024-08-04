<?php
//$db   = "C:\AccessControl\iCCard3000.mdb";
//$db1   = "C:\Program Files (x86)\UNIS\UNIS.mdb";
$db = 'C:\xampp\htdocs\att2000.mdb';
//$db   = "C:\Program Files\ACServer\ACCESS.mdb";
$user = "";
//$pass = "fdmsamho";
$pass = "";
//$pass = "168168";
$sleep = 1;

/*define('SMS_URL', 'http://120.28.124.79/SJSP/?module=SJSP&action=DTR');*/
/*define('SMS_URL', 'http://sjsp-sams.edu/');
/*define('DTR_URL', 'http://sjsp-sams.edu/get-dtr');*/


define('SMS_URL', 'http://127.0.0.1:8001/api/received-attendance');
define('DTR_URL', 'http://127.0.0.1:8001/api/last-attendance');

define('LOCAL_URL', 'http://127.0.0.1:8000/api/received-attendance');


/*120.28.86.165*/
?>