<?php
return;
// ini_set("extension", "/app/.dk189/php-ext/mbstring.so");
// ini_set("display_errors", 1);
// error_reporting(E_ALL);

require "./.dk189/config.php";

$fdb = new \Google\Firebase\DB(SQT_Firebase_RealtimeDB_Url, SQT_Firebase_RealtimeDB_Auth);

json_encode(date_default_timezone_set("Asia/Ho_Chi_Minh"));
$arr = array();
$arr['getallheaders']	= $header = getallheaders();
$arr['headers_list']	= headers_list();
$arr['_SERVER']		    = json_encode($_SERVER, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
$arr['REQUEST']		    = $_REQUEST;
$arr['GET']			    = $_GET;
$arr['POST']			= $_POST;

$arr['php_input']		= file_get_contents("php://input");
$php_input_parse        = json_decode($arr['php_input']);
$arr['php_input']       = $php_input_parse ? $php_input_parse : $arr['php_input'];

$arr['time']            = date("Y-m-d_H\hi\ms\s");
$arr["microtime"]       = microtime(true) * 10000;

$request_id = sprintf("%s_%s", $arr['time'], $arr['microtime']);

try {
    $firebase_response = $fdb->set("webhook-requests", urlencode($request_id), $arr);
}catch(\Exception $ex) {
    // var_dump($ex);
}

if (isset($header["user-agent"]) && $header["user-agent"] == "facebookexternalua") {
    $fb = new \Hooker\Facebook($fdb, SQT_Facebook_Page_Token);

    try {
        $fb->process($request_id, $arr['php_input']);
    }catch (\Exception $ex) {
        $firebase_response = $fdb->set("webhook-errors", urlencode($request_id), $ex->getMessage());
    }
} else if ( isset($_REQUEST['hub_challenge']) ) {
	echo $_REQUEST['hub_challenge'];
}


?>
