<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
error_log(".:: ".basename(__FILE__),0); // Debugging

// include('_db.php');
include('../../Overview/turk/turk_functions.php');
include("../../amtKeys.php");
include("../../isSandbox.php");

$AccessKey = $_REQUEST['accessKey']; 
$SecretKey = $_REQUEST['secretKey']; 

try {
	$dbh = getDatabaseHandle();
	// ChromePhp::log($dbh);
} catch( PDOException $e ) {
	ChromePhp::warn("something happened w/pdo connect"); 
	echo $e->getMessage();
}

// Retrieve HITs from database (for specific task and either for Sandbox or productive MTurk mode)
if( $dbh ) {

	$task = $_REQUEST['task'];

	$resultHitIds = array();
	$resultHits = array();

	$sql = "SELECT hit_Id FROM hits WHERE task = :task AND sandbox = :sandbox";
	# $sql = "SELECT hit_Id FROM hits WHERE task = :task";
	$sth = $dbh->prepare($sql);

	//echo "sandbox is ";
	//echo "$SANDBOX"; 
	//echo "...\n"; 

	$sql_params = array(":task" => $task, ":sandbox" => $SANDBOX); 
	//ChromePhp::log("sql params", $sql_params); 
	$sth->execute($sql_params);
	# $sth->execute(array(':task' => $task));

	$hitsForTask = array(); 
	try {
		$hitsForTask = $sth->fetchAll();
	} catch (Exception $e) {
		//ChromePhp::log($e);
	}

	//echo "\nhitsForTask --> \n"; 
	//print_r($hitsForTask);

	// FIXME REMOVE 
	// $hitsForTask = ["37SOB9Z0SSQR1ZGTKLJJG19QSL4L3O"]; 
	// ChromePhp::log("hitsfortask", $hitsForTask);

	$reviewableHits = turk50_getAllReviewableHits("reviewable");
	// ChromePhp::log("reviewable hits", $reviewableHits);

	//echo "\nhitsFromTurk --> \n"; 
	//print_r($hitsFromTurk); 

	foreach($reviewableHits as $hit){
		foreach($hitsForTask as $hit){
			if(in_array($hit["hit_Id"], $reviewableHits)){
				array_push($resultHitIds, $hit["hit_Id"]);
			}

		}
	}

	$resultHitIds = array_unique($resultHitIds);
	// ChromePhp::log("result hit ids", $resultHitIds);
	//echo "\nresultHitIds --> \n";
	//print_r($resultHitIds);

	foreach($resultHitIds as $hitId){
		//print_r(turk_easyHitToAssn($hitId));
		//echo "</br></br>";
		$hitInfo = turk_easyHitToAssn($hitId);
		ChromePhp::log($hitInfo); 
		if($hitInfo["NumResults"] <= 0){
			ChromePhp::log("numresults", $hitInfo["NumResults"]); 

			try {
				turk_easyDispose($hitId);
				sleep(.25);
				$sql = ("DELETE FROM hits WHERE hit_Id = :hit_Id");
				$sth = $dbh->prepare($sql);
				$sth->execute(array(':hit_Id' => $hitId));
			} catch (Exception $e) {
				ChromePhp::warn("Something went wrong inside getAllReviewableHits!", $e); 
			} finally {
				ChromePhp::log("Done with disposing hits inside getAllReviewableHits"); 
			}
		} else { 
			array_push($resultHits, $hitInfo["Assignments"]); 
			// ChromePhp::log("resultHits", $resultHits);
		}
	}

	//echo "\njson encoding resultHits --> \n";
	echo json_encode($resultHits);

}
else {
	echo "FAILED TO ACQUIRE DB HANDLE!";
}

?>
