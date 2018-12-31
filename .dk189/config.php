<?php
require "function.php";
require "loader.php";

define("JSONENV_CUSTOM_FILE_PATH", false);
    // false or String: if is string, load env in this file if exists. if false, load .env.json in start directory.
define("JSONENV_FORCE_OVERRIDE", false);
    // Override default env by data in jsonenv.php
require "jsonenv.php";
    // Load environment variables in .env.json
// If you cannot config env for your system, you can create json object in file ".env.json"



define("SQT_Facebook_Page_Token", getenv("SQT_Facebook_Page_Token"));

define("SQT_Firebase_RealtimeDB_Url", getenv("SQT_Firebase_RealtimeDB_Url"));
define("SQT_Firebase_RealtimeDB_Auth", getenv("SQT_Firebase_RealtimeDB_Auth"));


define("SQT_Crypter_KEY", getenv("SQT_Crypter_KEY"));
define("SQT_Crypter_IV", getenv("SQT_Crypter_IV"));


register_namespace( rtrim(__DIR__) . "/libraries");


setCrypterSecret(SQT_Crypter_KEY, SQT_Crypter_IV);

$fdb = new \Google\Firebase\DB(SQT_Firebase_RealtimeDB_Url, SQT_Firebase_RealtimeDB_Auth);
$fb = new \Hooker\Facebook($fdb, SQT_Facebook_Page_Token);
var_dump(SQT_Crypter_IV);
?>
