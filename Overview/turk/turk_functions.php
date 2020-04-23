<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

include("../../Retainer/php/_db.php");

// AWS SDK
require '../../Retainer/php/lib/aws-autoloader.php';

// REMOVE FOR PRODUCTION
//
//
//
//
//
//
//
//
//
include '../../// ChromePhp.php';
//
//
//
//
//
//
//
//
//
//
//
//
//

$PAGE_SIZE = 100;  // Number of HITs per 'page'

// $AccessKey = $_REQUEST['accessKey']; 
// $SecretKey = $_REQUEST['secretKey'];
// $SANDBOX   = $_REQUEST['useSandbox'];


/**
 * creates a client object for AWS either for MTurk's Sandbox or Production environments  
 * @param $sandbox Whether to use sandbox (boolean, true => sandbox)
 * @param $accessKey AWS MTurk IAM Access Key
 * @param $secretKey AWS MTurk IAM Secret Key
 * @return \Aws\MTurk\MTurkClient Client object
 */
function createClient($sandbox, $accessKey, $secretKey) {

	// Put credentials in array for API call
	$credentialsArray = array(
		"key" => $accessKey,
		"secret" => $secretKey,
	);

	// Request AWS SDK MTurk client either for sandbox or production MTurk
	if ($sandbox) {
		$client = new Aws\MTurk\MTurkClient([
			'version' => 'latest',
			'region' => 'us-east-1',
			'endpoint' => 'https://mturk-requester-sandbox.us-east-1.amazonaws.com', // Use sandbox
			'credentials' => $credentialsArray
		]);
	} else {
		$client = new Aws\MTurk\MTurkClient([
			'version' => 'latest',
			'region' => 'us-east-1',
			'endpoint' => 'https://mturk-requester.us-east-1.amazonaws.com', // Use production MTurk
			'credentials' => $credentialsArray
		]);
	}

	return $client;
}

function turk50_hit($title,$description,$money,$url,$duration,$lifetime,$qualification,$maxAssignments,$keywords,$AutoApprovalDelayInSeconds) {
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey); 

	// prepare ExternalQuestion
	$Question =
		"<ExternalQuestion xmlns='http://mechanicalturk.amazonaws.com/AWSMechanicalTurkDataSchemas/2006-07-14/ExternalQuestion.xsd'>" .
		"<ExternalURL>$url</ExternalURL>" .
		"<FrameHeight>800</FrameHeight>" .
		"</ExternalQuestion>";

	// prepare Request
	$request = array(
		"Title" => $title,
		"Description" => $description,
		"Question" => $Question,
		"Reward" => (string) $money,
		"AssignmentDurationInSeconds" => $duration + 0,
		"LifetimeInSeconds" => $lifetime + 0,
		"QualificationRequirements" => $qualification,
		"MaxAssignments" => $maxAssignments + 0,
		"Keywords" => $keywords,
		"AutoApprovalDelayInSeconds" => $AutoApprovalDelayInSeconds + 0
	);

	try {
		// invoke MTurk SDK 
		// ChromePhp::log($client);
		$HITResponse = $client->CreateHIT($request);
		// ChromePhp::log($client);
		print_r($HITResponse); 
		return $HITResponse;
	} catch (RequestException $e) {
		echo "Error with HIT creation"; 
		print_r($e); 	
	}
	finally {
		// log the function 
		// ChromePhp::log("Returning from turk50_hit()");
	}

}


function turk_easyApprove($assignmentNumber, $encouragement="Great job.") {
	//venar303@gmail.com account
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey); 

	$request = array(
		"AssignmentId" => $assignmentNumber,
		"OverrideRejection" => true,
		"RequesterFeedback"=> $encouragement
	); 

	try {
		// invoke MTurk SDK 
		$approveAssignmentResponse = $client->approveAssignment($request);
	} catch (RequestException $e) {
		echo "Error with HIT approval"; 
		// ChromePhp::warn($e);
		print_r($e); 	
	}
	finally {
		// log the function 
		// ChromePhp::log("Returning from turk_easyApprove()");
	}
}	


function turk_easyReject($assignmentNumber, $encouragement="") {
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey); 

	$request = array(
		"AssignmentId" => $assignmentNumber,
		"RequesterFeedback"=> $encouragement
	); 

	try {
		// invoke MTurk SDK 
		$response= $client->rejectAssignment($request);
		return $client;
	} catch (RequestException $e) {
		echo "Error with HIT rejection"; 
		print_r($e); 	
	}
	finally {
		// log the function 
		// ChromePhp::log("Returning from turk_easyReject()");
	}
}


function turk_easyBonus($worker_id, $assignmentNumber, $bonus, $reason) { 

	global $SANDBOX, $DEBUG, $AccessKey, $SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey); 

	$request = array(
		"AssignmentId" => $assignmentNumber,
		"BonusAmount" => $bonus, 
		"Reason" => $reason,
		"WorkerId" => $worker_id
	); 

	try {
		// invoke MTurk SDK 
		$response= $client->sendBonus($request);
		return $client;
	} catch (RequestException $e) {
		echo "Error with HIT bonusing"; 
		print_r($e); 	
	}
	finally {
		// log the function 
		// ChromePhp::log("Returning from turk_easyBonus()");
	}
}


function turk_easyDispose($hitId) {
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey); 

	$request = array(
		"HITId" => $hitId
	); 

	try {
		// invoke MTurk SDK 
		$response= $client->deleteHIT($request);
		return $client;
	} catch (RequestException $e) {
		echo "Error with HIT deletion"; 
		print_r($e); 	
	}
	finally {
		// log the function 
		// ChromePhp::log("Returning from turk_easyDispose()");
	}
}


function turk50_getAllReviewableHits($typeOfReview) {
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey, $PAGE_SIZE;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey);

	try {
		$request = array(
		);

		// invoke MTurk SDK 
		$response = null; 
		if ($typeOfReview == "all") {
			$response = $client->listHITs($request);
		} else {
			$response = $client->listReviewableHITs($request);
		}	

		$nextToken = $response["NextToken"]; 
		$numPages = ceil($response["NumResults"] / $PAGE_SIZE);
		// ChromePhp::log("numresults is", $response["NumResults"]);

		$resultArray = array(); 
		for($i = 0; $i < count($response["HITs"]); $i++) { 
			$resultArray[] = $response["HITs"][$i]["HITId"];
		}

		// if need to loop through all results, get the NextPageToken	
		for($i = 2; $i < $numPages; $i++) {
			$request = array(
				"NextToken" => $nextToken
			); 

			$response = null; 
			if ($typeOfReview == "all") {
				$response = $client->listHITs($request);
			} else {
				$response = $client->listReviewableHITs($request);
			}	
			$nextToken = $response["NextToken"]; 

			// add the remaining HITs 
			for($j = 0; $j < count($response["HITs"]); $j++) { 
				$resultArray[] = $response["HITs"][$j]["HITId"];
			}
		}	

		// return array 
		// // ChromePhp::log($resultArray);
		return $resultArray; 


	} catch (RequestException $e) {
		echo "Error with listing of reviewable HITs";
		print_r($e); 
	}
	finally {
		// log the function 
		// ChromePhp::log("Returning from turk_getAllReviewableHits()");
	}
}


function turk_easyExpireHit($hitId) {
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey);

	// update the ExpireAt parameter to some time in the past to immediate expire the HIT
	$request = array(
		"HITId" => $hitId, 
		"ExpireAt" => new DateTime('2000-01-01')
	);

	try {
		// invoke MTurk SDK
		$response= $client->updateExpirationForHIT($request);
		return $response;
	} catch (RequestException $e) {
		echo "Error with HIT expiring";
		print_r($e);
	}
	finally {
		// log the function
		// ChromePhp::log("Returning from turk_easyExpireHit()");
	}

	// 1. get a list of all HITs
	// 2. go through them and update expiration time to be in the past (which immediately expires them) 




}


function turk_easyHitToAssn($hitId) {
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey);

	$request = array(
		"HITId" => $hitId
	);

	try {
		// invoke MTurk SDK
		$response= $client->listAssignmentsForHIT($request);
		return $response;
	} catch (RequestException $e) {
		echo "Error with HIT listing assignments for HIT";
		print_r($e);
	}
	finally {
		// log the function
		// ChromePhp::log("Returning from turk_easyHitToAssn()");
	}

}

function turk50_getHit($hitId){
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey);

	$request = array(
		"HITId" => $hitId
	);

	try {
		// invoke MTurk SDK
		$response= $client->getHIT($request);
		return $response;
	} catch (RequestException $e) {
		echo "Error with HIT getting";
		print_r($e);
	}
	finally {
		// log the function
		// ChromePhp::log("Returning from turk_getHit()");
	}
}

function turk50_getAccountBalance(){
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	if($SANDBOX)
		$turk50 = new Turk50($AccessKey, $SecretKey);
	else
		$turk50 = new Turk50($AccessKey, $SecretKey, array("sandbox" => FALSE));

	$Request = array(
		"HITId" => $hitId
	);

	return $turk50->GetAccountBalance();
}

function turk50_createQualificationType($name, $description, $keywords, $qualSandbox){
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey);

	$request = array(
		"Name" => $name, 
		"Description" => $description, 
		"Keywords" => $keywords,
		"QualificationTypeStatus" => "Active", 
		"AutoGranted" => true
	);

	try {
		// invoke MTurk SDK
		$response= $client->createQualificationType($request);
		return $response;
	} catch (RequestException $e) {
		echo "Error with creating qualification type";
		print_r($e);
	}
	finally {
		// log the function
		// ChromePhp::log("Returning from turk_createQualificationType()");
	}

}

function turk50_disposeQualificationType($qualificationTypeId){
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey);

	$request = array(
		"QualificationTypeId" => $qualificationTypeId
	);

	try {
		// invoke MTurk SDK
		$response= $client->deleteQualificationType($request);
		return $response;
	} catch (RequestException $e) {
		echo "Error with disposing of qualification type";
		print_r($e);
	}
	finally {
		// log the function
		// ChromePhp::log("Returning from turk_disposeQualificationType()");
	}
}

function turk50_assignQualification($workerId, $qualificationTypeId, $qualSandbox){
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey);

	$request = array(
		"IntegerValue" => 1,
		"QualificationTypeId" => $qualificationTypeId,
		"WorkerId" => $workerId
	);

	try {
		// invoke MTurk SDK
		$response= $client->associateQualificationWithWorker($request);
		return $response;
	} catch (RequestException $e) {
		echo "Error with assigning of qualification type";
		print_r($e);
	}
	finally {
		// log the function
		// ChromePhp::log("Returning from turk_assignQualification()");
	}
}

function turk50_revokeQualification($workerId, $qualificationTypeId, $qualSandbox){
	global $DEBUG, $SANDBOX, $AccessKey ,$SecretKey;

	// create the MTurk client object 
	$client = createClient($SANDBOX, $AccessKey, $SecretKey);

	$request = array(
		"IntegerValue" => 1,
		"QualificationTypeId" => $qualificationTypeId,
		"WorkerId" => $workerId
	);

	try {
		// invoke MTurk SDK
		$response= $client->disassociateQualificationFromWorker($request);
		return $response;
	} catch (RequestException $e) {
		echo "Error with revoking of qualification type";
		print_r($e);
	}
	finally {
		// log the function
		// ChromePhp::log("Returning from turk_revokeQualification()");
	}
}        

?>
