<?php

function CheckRequirement() {
    global $argv;
    $error = false;
    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        printf("%s\n", "Your PHP Version is: " . PHP_VERSION . ". Recommend PHP Version is PHP 5.4 .");
    }
    $curl = isCallable('curl_init');
    if(!$curl) {
        printf("%s\n", "Wordpress Vulnerability Scanner require cURL Extension.");
        $error = true;
    }
    // nawawi: md5_file need this
    if ( !ini_get('allow_url_fopen') ) {
        echo "Wordpress Vulnerability Scanner require allow_url_fopen set to true\n";
        $error = true;
    }
    if($error) exit;
}

function isCallable($function = '') {
    if(!function_exists($function) OR !is_callable($function) OR (strpos(ini_get('disable_functions'), $function) !== false)) {
        return false;
    } else {
        return true;
    }
}
?>
