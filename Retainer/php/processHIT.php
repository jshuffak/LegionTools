<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
error_log(".:: ".basename(__FILE__),0); // Debugging

// include('_db.php');
include('../../Overview/turk/turk_functions.php');
include("../../amtKeys.php");
include("../../isSandbox.php");

// AWS SDK
// require 'lib/aws-autoloader.php';

$AccessKey = $_REQUEST['accessKey']; 
$SecretKey = $_REQUEST['secretKey'];

// Try to get database handle
try {
	$dbh = getDatabaseHandle();
} catch( PDOException $e ) {
	echo $e->getMessage();
}

if( $dbh ) {

	// Get parameters from request data
	$id = $_REQUEST['id']; // Either AssignmentId or HITId
	$operation = $_REQUEST['operation'];

	// $assignmentInfo = turk_easyHitToAssn($hitId);
	// $assignmentId = $assignmentInfo["Assignment"]["AssignmentId"];

	// Branch into operation
	// Approve
	if($operation == "Approve"){
		turk_easyApprove($id); //AssignmentId
	}
	// Bonusing - note: this is new bonus, old code is below under "Bonus"
	else if($operation == "Bonusing"){
		//
	}
	// Unreject
	else if($operation == "Unreject"){

		$workerId = $_REQUEST['workerId'];

		// Initiate log file - can be called via: echo "\n\n$currentTime - Message";
		//        $logFilePath = './debug.txt';
		//        ob_start();
		//        if (file_exists($logFilePath)) {
		//            include($logFilePath);
		//        }
		//        $currentTime = date(DATE_RSS);

		// Obtain boolean whether sandbox is used
		$sandbox = $_REQUEST['useSandbox'];

		// Put credentials in array for API call
		$credentialsArray = array(
			"key" => $AccessKey,
			"secret" => $SecretKey,
		);

		// Request AWS SDK MTurk client either for sandbox or productive MTurk
		if($sandbox) {
			$client = new Aws\MTurk\MTurkClient([
				'version' => 'latest',
				'region'  => 'us-east-1',
				'endpoint' => 'https://mturk-requester-sandbox.us-east-1.amazonaws.com', // Use sandbox
				'credentials' => $credentialsArray
			]);
		} else {
			$client = new Aws\MTurk\MTurkClient([
				'version' => 'latest',
				'region'  => 'us-east-1',
				'endpoint' => 'https://mturk-requester.us-east-1.amazonaws.com', // Use productive MTurk
				'credentials' => $credentialsArray
			]);
		}

		$result = $client->approveAssignment([
			'AssignmentId' => $id,
			'WorkerId' => $workerId,
			'OverrideRejection' => true, // Override causes unreject
			'RequesterFeedback' => 'Amazing job.',
		]);

		echo "\n\n$currentTime - Status: " . $result . " Sandbox: " . $sandbox;

		// Log output to file
		//        $logFile = fopen($logFilePath, 'w');
		//        fwrite($logFile, ob_get_contents());
		//        fclose($logFile);
		//        ob_end_flush();

		//return;
	}
	// Reject
	else if($operation == "Reject"){
		$mt = turk_easyReject($id); //AssignmentId
	}
	// Bonus
	else if($operation == "Bonus"){
		if(isset($_REQUEST['reason'])){
			$reason = $_REQUEST['reason'];
		}
		else $reason = "Did extra work.";
		$mt = turk_easyBonus($_REQUEST['workerId'], $id, $_REQUEST['amount'], $reason);
		// print_r($mt);
	}
	// Dispose
	else if($operation == "Dispose"){
		turk_easyDispose($id); //HITId
		
		// Remove from DB
		$sql = ("DELETE FROM hits WHERE hit_Id = :hit_Id");
		$sth = $dbh->prepare($sql); 
		$sth->execute(array(':hit_Id' => $id));
	}

}

?>
