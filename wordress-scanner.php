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

date_default_timezone_set('Asia/Kuala_Lumpur');
define('ROOT_PATH', dirname(__FILE__));
define('Version', '0.11beta');

if( strtolower(php_sapi_name()) != 'cli' ) {
	printf("%s\n", "Please run only from command line internface.");
	exit;
}

require_once(ROOT_PATH . '/base/load.php');
banner();
check_requirement();
check_version();

$target = (stripos($argv[1], 'http') === false) ? 'http://' . $argv[1] : $argv[1];

$found_plugin = false;
$e_plugins = false;
$found_e_plugin = false;

$wpscan = new WPScan( $target );
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
if($wpscan->sdb_path) {
	msg("[+] Found script for database replacing: " . $wpscan->sdb_path);
}
if($wpscan->is_multisite) {
	msg("[+] Multi-site enabled (http://codex.wordpress.org/Glossary#Multisite)");
}
if($wpscan->registration_enabled) {
	msg("[+] Registration enabled! ");
}
if($wpscan->xmlrpc_path) {
	msg("[+] XML-RPC Interface available under " . $wpscan->xmlrpc_path);
}

msg("");
msg("[+] Looking for visible plugins on homepage...");
msg("");
$wpscan->search_plugins();

if($wpscan->list_plugins) {
	foreach($wpscan->list_plugins as $plugin) {
		msg("[+] Found {$plugin['plugin_name']} plugin.");
		if(isset($plugin['url'])) {
			msg("[!] Plugin URL: {$plugin['url']}");
		}
		if(isset($plugin['svn'])) {
			msg("[!] Plugin SVN: {$plugin['svn']}");
		}
		msg("");
	}
	$found_plugin = true;
} else {
	msg("[-] No plugin was found.");
}

msg("");
print "[!] Enumerate plugins name? [y/N] ";
$answer = strtolower( trim( fgets(STDIN) ) );

if( (!empty($answer)) AND $answer == 'y') {
	msg("[+] Wordpress Plugin Database - Revision 678288");
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
	print "[!] Start searching for plugin vulnerability? [y/N] ";
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
