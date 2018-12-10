<?php
require "function.php";
require "loader.php";

define("SQT_Facebook_Page_Token", getenv("SQT_Facebook_Page_Token"));

define("SQT_Firebase_RealtimeDB_Url", getenv("SQT_Firebase_RealtimeDB_Url"));
define("SQT_Firebase_RealtimeDB_Auth", getenv("SQT_Firebase_RealtimeDB_Auth"));


define("SQT_Crypter_KEY", getenv("SQT_Crypter_KEY"));
define("SQT_Crypter_IV", getenv("SQT_Crypter_IV"));


register_namespace( rtrim(__DIR__) . "/libraries");


setCrypterSecret(SQT_Crypter_KEY, SQT_Crypter_IV);

$fdb = new \Google\Firebase\DB(SQT_Firebase_RealtimeDB_Url, SQT_Firebase_RealtimeDB_Auth);
$fb = new \Hooker\Facebook($fdb, SQT_Facebook_Page_Token);
?>
