<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
error_log(".:: ".basename(__FILE__),0); // Debugging

include("_db.php");

try {
  $dbh = getDatabaseHandle();
} catch( PDOException $e ) {
  echo $e->getMessage();
}


if( $dbh ) {

	$task = $_REQUEST['task'];

	$sql = "DELETE FROM retainer where task = :task";
	$sth = $dbh->prepare($sql); 
	$sth->execute(array(':task' => $task));
}

?>
