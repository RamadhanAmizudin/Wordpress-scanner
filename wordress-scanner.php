<?php
/**
	A Wordpress Scaner
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

date_default_timezone_set('Asia/Kuala_Lumpur');
define('ROOT_PATH', dirname(__FILE__));

if( strtolower(php_sapi_name()) != 'cli' ) {
	printf("%s\n", "Please run only from command line internface.");
	exit;
}

require_once(ROOT_PATH . '/base/load.php');
banner();
check_requirement();

$found_plugin = false;
$e_plugins = false;
$found_e_plugin = false;

$wpscan = new WPScan($argv[1]);
msg("[+] Target: " . $wpscan->url);
$start_time = time();
msg("[+] Start Time: " . date('d-m-Y h:iA', $start_time));

if( !$wpscan->is_wordpress() ) {
	msg("[-] This site does not seem to be running WordPress!");
	exit;
}

$wpscan->parser();
$version = $wpscan->get_version();

if($version) {
	msg(vsprintf("[+] Wordpress Version %s, using %s method", $version));
}
if($wpscan->theme_name) {
	msg("[+] Target using {$wpscan->theme_name} theme.");
}
if($wpscan->robots_path) {
	msg("[+] robots.txt available at " . $wpscan->robots_path);
}
if($wpscan->readme_path) {
	msg("[+] Wordpress Readme file at " . $wpscan->readme_path);
}
if($wpscan->xmlrpc_path) {
	msg("[+] XML-RPC Interface available under " . $wpscan->xmlrpc_path);
}

msg("[+] Looking for visible plugins on homepage...");
$wpscan->search_plugins();

if($wpscan->list_plugins) {
	foreach($wpscan->list_plugins as $plugin) {
		msg("[+] Found {$plugin} plugin.");
	}
	$found_plugin = true;
} else {
	msg("[-] No plugin was found.");
}

msg("");
print "[!] Enumerate plugins name? [y/n] ";
$answer = strtolower( trim( fgets(STDIN) ) );

if($answer == 'y') {
	msg("[+] Wordpress Plugin Database - Revision 668735");
	msg("[!] Warning: This may take a while!");
	$wpplugin = new WPPlugin($wpscan->url);
	msg("[!] Total {$wpplugin->total_plugins} plugins!");
	$e_plugins = $wpplugin->enumerate();
	if(is_array($e_plugins) AND $found_plugin === false) {
		$found_plugin = true;
		$wpscan->list_plugins = $e_plugins;
	} else {
		$wpscan->list_plugins = array_merge($wpscan->list_plugins, $e_plugins);
	}
}

if($found_plugin) {
	msg("");
	print "[!] Start searching for plugin vulnerability? [y/n] ";
	$answer = strtolower( trim( fgets(STDIN) ) );
	if($answer == 'y') {
		$wpvuln = new WPVuln;
		$wpvuln->plugin($wpscan->list_plugins);
	}
}

msg("");
$end_time = time();
msg("[+] Finish Scan at " . date('d-m-Y h:iA', $end_time));
msg("[+] Total time taken is: " . round(($end_time - $start_time), 4) . " seconds");
?>