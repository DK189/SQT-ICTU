<?php
// ini_set("extension", "/app/.dk189/php-ext/mbstring.so");
// ini_set("display_errors", 1);
// error_reporting(E_ALL);

require "./.dk189/config.php";

$_SERVER["ACCESS_TOKEN"] = false;

function token ($token = false) {
    $_SERVER["ACCESS_TOKEN"] = !$token ? $_SERVER["ACCESS_TOKEN"] : json_decode(decrypt($token));
    return $_SERVER["ACCESS_TOKEN"];
}

$_HEADER = getallheaders();

if (isset($_HEADER["access-token"])) {
    token($_HEADER["access-token"]);
}

$Machine = new \TNU\Machine();

$action = isset($_GET["action"]) ? $_GET["action"] : false;

if ($action == "phpinfo") {
    phpinfo(INFO_ALL);
    return;
}

if ($action === "login") {
    if ($Machine->login($_POST['username'], md5($_POST['password']))) {
        $now = microtime(true);
        $nowTxt = "" . $now . "";
        $nowMd5 = md5($nowTxt);
        $cert = array(
            "prefixTime" => strrev($nowMd5),
            "username" => $_POST['username'],
            "currentTime" => $now,
            "password" => $_POST['password'],
            "suffixTime" => $nowMd5,
        );
        $certTxt = encrypt(jval($cert));
        jshow($certTxt);
    } else {
        jshow(false);
    }
} else {
    if (!!token() && $Machine->login(token()->username, md5(token()->password))) {
        switch ($action) {
            case 'test': {

                break;
            }
            case "profile": {
                jshow($Machine->getStudent());
                break;
            }
            case "semester":
            case "semester-study": {
                jshow($Machine->getSemesterOfStudy());
                break;
            }
            case "time-table": {
                $tkb = $Machine->getTimeTableOfStudy(isset($_POST["semester"]) ? $_POST["semester"] : true);
                jshow($tkb);
                break;
            }
            case "semester-exam": {
                jshow($Machine->getSemesterOfTest());
                break;
            }
            case "exam-table": {
                $tkb = $Machine->getTimeTableOfTest(isset($_POST["semester"]) ? $_POST["semester"] : true);
                jshow($tkb);
                break;
            }
            case "mark": {
                jshow($Machine->getMarkTable());
                break;
            }
            default:
                jshow(NULL);
                break;
        }
    } else {
        jshow(false);
    }
}

return;
?>
