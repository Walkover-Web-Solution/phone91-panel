<?php
/**
 * 
 * Phone91 mobile varification
 * 
 * @author "Vikas Wasiya" <vikas@hostnsoft.com>
 * 
 */

//error_reporting(E_ALL);
date_default_timezone_set('Asia/Calcutta');
//include_once('/home/voicepho/public_html/classes/dbconnect_class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/classes/dbconnect_class.php');
//include_once('/var/lib/asterisk/agi-bin/fun.php');
//include_once('/var/lib/asterisk/agi-bin/phpagi-2.20/phpagi.php');

//$db_obj = new db_class();

function save_db($mobile_no,$callerID,$uid)
{
	$db_obj = new db_class();
	$con = $db_obj->msg91_connect();
	$error = '';
	$sql = "insert into msg91_status (unique_id,mobile_no,status,code) VALUES ('$uid','$mobile_no','Missed Call','$callerID')";
	$result = mysql_query($sql,$con) or $error="Error";
	mysql_close($con);
	if ($error != '')
	{
		echo $error;
		return $error;
		die();
	}
}

function callfile($mobile_no,$callerID,$uid)
{
	$dir = "/tmp";
	$File = "ivoice1".$uid.".call";
	$path = $dir."/".$File;
	
	$Handle = fopen($path, 'w');

	$Data = "Set: PassedInfo=".$uid."\n";
	fwrite($Handle, $Data);
	$Data = "Channel: SIP/ivoice3/".$mobile_no."\n";
	fwrite($Handle, $Data);
	$Data = "CallerId: ".$callerID."\n";
	fwrite($Handle, $Data);
	$Data = "WaitTime: 20\n";
        fwrite($Handle, $Data);
	$Data = "Context: mverify\n";
	fwrite($Handle, $Data);
	$Data = "Extension: 111\n";
	fwrite($Handle, $Data);
	$Data = "Priority: 1\n";
	fwrite($Handle, $Data);
	fclose($Handle);
//	error_reporting(-1);

	rename($path, "/var/spool/asterisk/outgoing/".$File);
}

$mobile_no = isset($_REQUEST['userNumber']) ? trim($_REQUEST['userNumber']) : "";
$callerID = isset($_REQUEST['randomNumber']) ? trim($_REQUEST['randomNumber']) : "";

$uid = time().$mobile_no;

if ( $mobile_no=='' || $callerID=='' )
{
	echo "Invalid parameters\n";
	return "Invalid parameters";
	die();
}

if (!(is_numeric($mobile_no)) || strlen($mobile_no) < 6 || !(is_numeric($callerID)) || strlen($callerID) < 6)
{
        echo "Numbers are not valid";
	return "Numbers are not valid";
        die();
}

save_db($mobile_no,$callerID,$uid);

callfile($mobile_no,$callerID,$uid);
echo $uid;

?>
