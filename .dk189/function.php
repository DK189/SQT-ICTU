<?php

if (!function_exists('getallheaders')) {
    function getallheaders() {
       $headers = array ();
       foreach ($_SERVER as $name => $value) {
           if (substr($name, 0, 5) == 'HTTP_') {
               $headers[str_replace(' ', '-', (strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
}

function setCrypterSecret($key = "https://github.com/DK189/SQT-ICTU", $iv = "kingdark.org") {
    $_SERVER["secret_key"] = $key;
    $_SERVER["secret_iv"] = $iv;
}

function encrypt($string) {
    if (
        !isset($_SERVER["secret_key"])
        ||
        empty($_SERVER["secret_key"])
        ||
        !isset($_SERVER["secret_iv"])
        ||
        empty($_SERVER["secret_iv"])
    ) {
        setCrypterSecret();
    }

    $key = hash('sha256', $_SERVER["secret_key"]);
    $iv = substr(hash('sha256', $_SERVER["secret_iv"]), 0, 16);

    $output = openssl_encrypt($string, "aes-256-cfb", $key, 0, $iv);
    $output = base64_encode($output);

    return $output;
}

function decrypt($string) {
    if (
        !isset($_SERVER["secret_key"])
        ||
        empty($_SERVER["secret_key"])
        ||
        !isset($_SERVER["secret_iv"])
        ||
        empty($_SERVER["secret_iv"])
    ) {
        setCrypterSecret();
    }

    $key = hash('sha256', $_SERVER["secret_key"]);
    $iv = substr(hash('sha256', $_SERVER["secret_iv"]), 0, 16);

    $string = base64_decode($string);
    $output = openssl_decrypt($string, "aes-256-cfb", $key, 0, $iv);

    return $output;
}

function jval () {
    if (func_num_args() == 1) {
        return json_encode(func_get_arg(0));
    } else {
        return json_encode(func_get_args());
    }
}

function jshow () {
    if (func_num_args() == 1) {
        echo json_encode(func_get_arg(0));
    } else {
        echo json_encode(func_get_args());
    }
}
