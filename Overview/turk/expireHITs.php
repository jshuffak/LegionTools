<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 10000);
set_time_limit ( 10000);

include("../../amtKeys.php");
include("../../config.php");
include("../../isSandbox.php");
include 'turk_functions.php';

$AccessKey = $_REQUEST['accessKey']; 
$SecretKey = $_REQUEST['secretKey'];

try {
	$dbh = getDatabaseHandle();
	// ChromePhp::log('Hello console!');
	// ChromePhp::log($_SERVER);
	// ChromePhp::warn('something went wrong!');
} catch(PDOException $e) {
	echo $e->getMessage();
}

function expireHit($hitId){
	global $dbh;
	turk_easyExpireHit($hitId);
	sleep(.25); //Give the HIT a moment to expire
	// turk_easyDispose($hitId);
	// sleep(.25); //Give the HIT a moment to dispose
}


$sql = ("SELECT * from hits WHERE task = :task");
$sth = $dbh->prepare($sql);
$sth->execute(array(':task' => $_REQUEST['task']));
$hits = $sth->fetchAll();

foreach ($hits as $hit) {
	$hitId = $hit["hit_Id"]; 
	$hitInfo = turk50_getHit($hitId);
	$hitInfo = $hitInfo["HIT"];

	ChromePhp::log($hitInfo); 
	// ChromePhp::log(property_exists($hitInfo, "HITStatus"));
	// if(property_exists($hitInfo, "HITStatus")){
	switch($hitInfo["HITStatus"]) {
		case "Disposed": 
			// expireHit($hitId);
			$sql = ("DELETE FROM hits WHERE hit_Id = :hit_Id");
			$sth = $dbh->prepare($sql);
			$sth->execute(array(':hit_Id' => $hitId));
			break; 
		case "Reviewable": 
			expireHit($hitId);
			sleep(.25);
			$sql = ("UPDATE hits SET assignable = 0 WHERE hit_Id = :hit_Id");
			$sth = $dbh->prepare($sql);
			$sth->execute(array(':hit_Id' => $hitId));
			break;
		default: 
			expireHit($hitId);
			sleep(.25);
	}
	sleep(1); //Don't overload mturk with getHit
}

?>
