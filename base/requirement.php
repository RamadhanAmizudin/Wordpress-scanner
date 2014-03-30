<?php
/**
	A Wordpress Scanner
	Copyright (C) 2013  Ramadhan Amizudin

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

function check_requirement() {
	$argv = $_SERVER['argv'];
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
		exit(1);
	}
}

function usage() {
	$argv = $_SERVER['argv'];
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
