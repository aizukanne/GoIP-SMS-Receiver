#!/usr/bin/php -f

<?php
require('db.php');
error_reporting(E_ALL | E_STRICT);

exec('ps -A | grep ' . escapeshellarg(basename(__FILE__)) , $results);
if (count($results) > 1) {
  echo "None Already Running\n";
  die(0);
}

$_ = $_SERVER['_'];  
$restartMyself = function () {
	global $_, $argv; 
    pcntl_exec($_, $argv);
    };
register_shutdown_function($restartMyself);
pcntl_signal(SIGTERM, $restartMyself);   
pcntl_signal(SIGHUP,  $restartMyself);   
pcntl_signal(SIGINT,  $restartMyself);   
set_error_handler($restartMyself , E_ALL); // Catch all errors


//....................sql procedure...................
$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$ip = '0.0.0.0';
// create a UDP socket
if(!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

// bind the source address
if( !socket_bind($sock, $ip, 44444) ) {
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Could not bind socket : [$errorcode] $errormsg \n");
}

$from = '';
$port = 0;
echo "Ready to receive connections...\n";

#loop forever
$bytes_received = socket_recvfrom($sock, $buf, 65536, 0, $from, $port);
if ($bytes_received == -1)
        die('An error occured while receiving from the socket');
while ($bytes_received <> -1){
	#echo "$from::$port >> $buf \n";
	list($firstword) = explode(':', $buf);
	if ($firstword == "req"){
      	list($a) = explode(';',$buf);
      	list($a1,$a2) = explode(':',$a);
      	$bufreg = "reg:".$a2.";status:200;";
		#Confirm Registration
      	$len = strlen($bufreg);
      	$bytes_sent = socket_sendto($sock, $bufreg, $len, 0, $from, $port);
	} elseif ($firstword == "RECEIVE"){
		#echo "$buf \n";
		list($a,$b,$c,$d,$e) = explode(';',$buf);
      	$a = explode(':',$a);
      	$msgid = $a[1];
      	$b = explode(':',$b);
      	$goipname = $b[1];
		$d = explode(':',$d);
		$srcnum = $d[1];
		$msg = substr($e,4);
      	$stmt = $pdo->prepare('INSERT INTO `receive` (`srcnum`, `msg`, `goipname`) VALUES (:srcnum, :msg, :goipname)');
      	$stmt->bindParam(':srcnum', $srcnum);
      	$stmt->bindParam(':msg', $msg);
      	$stmt->bindParam(':goipname', $goipname);
      	$stmt->execute();
		#echo "Sender: $srcnum \n"."Message: $msg \n";
                #Confirm message received
      			$bufrcv = "RECEIVE ".$msgid." OK";
                $bytes_sent = socket_sendto($sock, $bufrcv, strlen($bufrcv), 0, $from, $port);
      			#echo "$from : $port << $bufrcv \n";
	}
	$from = '';
        $port = 0;
        $buf = '';
	$bytes_received = socket_recvfrom($sock, $buf, 65536, 0, $from, $port);
}
?>
