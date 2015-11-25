<?php 

/**
 * @author Rahul Chordiya <rahul@hostnsoft.com>
 * @since 07 Aug 2013
 * @details file use to include all necessary files
 */
ini_set('memory_limit', '-1');
error_reporting(0);
ini_set('display_errors', 1);
date_default_timezone_set("Asia/Kolkata");
include_once dirname(dirname(__FILE__)).'/foundationCall.php';
//Include session file so we can maintain session for each page
include_once 'session.php';

//Include path page so that we can define all constant for our system
include_once 'definePath.php';

//Include regex constant page so that we can define all constant for our system
include_once 'defineConstant.php';


//include all necessary function
include_once "common_function.php";
include_once "function_layer.php";

include_once "logmonitor.php";

require_once "bugsnag.phar";
$bugsnag = new Bugsnag_Client(BUGSNAG_KEY);
set_error_handler(array($bugsnag, "errorHandler"));
set_exception_handler(array($bugsnag, "exceptionHandler"));

/************ Error Log Code start Here *************/
//Include Error talk class use to log each and every error warning notice
include_once CLASS_DIR."class.errortalk.php";
errorTalk::initialize(); 
errorTalk::errorTalk_Open();
/************ Error Log Code end Here *************/


?>