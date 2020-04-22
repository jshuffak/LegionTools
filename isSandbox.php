<?php


//uncomment and comment as neccesary to determine sandbox usage
//$SANDBOX=false;
$SANDBOX=1;

//if a URL parameter is preset, override the previous variable

if(isset($_REQUEST['useSandbox']))
{
	if($_REQUEST['useSandbox'] == 1) $SANDBOX = 1;
	else if ($_REQUEST['useSandbox'] == 0) $SANDBOX = 0;
}

?>
