<?php

function check_requirement() {
	global $argv;
	$error = false;
	if (version_compare(PHP_VERSION, '5.3.0', '<')) {
		printf("%s\n", "Your PHP Version is: " . PHP_VERSION . ". Recommend PHP Version is PHP 5.3 .");
	}
	$curl = isCallable('curl_init');
	if(!$curl) {
		printf("%s\n", "Wordpress Scanner require cURL Extension.");
		$error = true;
	}
    // nawawi: md5_file need this
    if ( !ini_get('allow_url_fopen') ) {
		echo "Wordpress Scanner require allow_url_fopen set to true\n";
		$error = true;
    }
	if($error) exit;
	if(!isset($argv[1])) {
		usage();
		exit;
	}
}

function usage() {
	global $argv;
	msg("");
	msg("[!] php {$argv[0]} <target>");
	msg("[!] php {$argv[0]} http://wordpress.org/");
	msg("[!] php {$argv[0]} http://wordpress.org/blog/");
}

function isCallable($function = '') {
	if(!function_exists($function) OR !is_callable($function) OR (strpos(ini_get('disable_functions'), $function) !== false)) {
		return false;
	} else {
		return true;
	}
}
