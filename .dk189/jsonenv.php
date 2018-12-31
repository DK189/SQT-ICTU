<?php
try {
    $fp = rtrim(getcwd(), "/\\") . "/" . ".env.json";
    if (defined("JSONENV_CUSTOM_FILE_PATH") && !!JSONENV_CUSTOM_FILE_PATH) {
        $fp = JSONENV_CUSTOM_FILE_PATH;
    }
    $fp = realpath($fp);
    if (!!$fp && !!file_exists($fp)) {
        $jsonContent = file_get_contents($fp);
        $json = json_decode($jsonContent, true);
        foreach($json as $key => $val) {
            if (!getenv($key) || (defined("JSONENV_FORCE_OVERRIDE") && !!JSONENV_FORCE_OVERRIDE)) {
                if (function_exists('apache_getenv') && function_exists('apache_setenv')) {
                    apache_setenv($key, $val);
                }
                if (function_exists('putenv')) {
                    putenv("$key=$val");
                }
                $_ENV[$key] = $val;
            }
        }
    }
} catch (\Exception $ex) {

}
?>
