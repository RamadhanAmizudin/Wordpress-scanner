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
define('ROOT_PATH', dirname(realpath(__FILE__)) );
define('Version', '0.30Beta');

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
$found_theme = false;
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
	msg("");
	print "[!] Start searching for wordpress version vulnerability? [y/N] ";
	$answer = strtolower( trim( fgets(STDIN) ) );
	if ($answer == 'y') {
		$wpvuln = new WPVuln('version');
		$wpvuln->version($version['version']);
	}
	msg("");
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
if($wpscan->theme_name) {
	msg("[+] Target is using {$wpscan->theme_name} theme");
	$theme[] = array('theme_name' => $wpscan->theme_name); 
}

msg("");
print "[!] Enumerate themes name? [y/N] ";
$answer = strtolower( trim( fgets(STDIN) ) );

if($answer == 'y') {
	msg("[!] Warning: This may take a while!");
	$wptheme = new WPTheme($wpscan->url);
	msg("[!] Total {$wptheme->total_themes} themes!");
	$e_themes = $wptheme->enumerate();
	if(is_array($e_themes)) {
		$found_theme = true;
	}
}

if($found_theme) {
	$theme = array_unique(array_merge($theme, $e_themes));
}
msg("");
print "[!] Start searching for theme vulnerability? [y/N] ";
$answer = strtolower( trim( fgets(STDIN) ) );
if($answer == 'y') {
	$wpvuln = new WPVuln('theme');
	$wpvuln->vuln($theme);
}

msg("");
msg("[+] Looking for visible plugins on homepage...");
$wpscan->search_plugins();

if($wpscan->list_plugins) {
	foreach($wpscan->list_plugins as $plugin) {
		msg("");
		msg("[+] Found {$plugin['plugin_name']} plugin.");
		if(isset($plugin['url'])) {
			msg("[!] Plugin URL: {$plugin['url']}");
		}
		if(isset($plugin['svn'])) {
			msg("[!] Plugin SVN: {$plugin['svn']}");
		}
	}
	$found_plugin = true;
} else {
	msg("[-] No plugin was found.");
}

msg("");
print "[!] Enumerate plugins name? [y/N] ";
$answer = strtolower( trim( fgets(STDIN) ) );

if($answer == 'y') {
	msg("[!] Warning: This may take a while!");
	$wpplugin = new WPPlugin($wpscan->url);
	msg("[!] Total {$wpplugin->total_plugins} plugins!");
	$e_plugins = $wpplugin->enumerate();
	if(is_array($e_plugins) AND $found_plugin === false) {
		$found_plugin = true;
		$wpscan->list_plugins = $e_plugins;
	} else {
		$wpscan->list_plugins = array_unique(array_merge($wpscan->list_plugins, $e_plugins));
	}
}

if($found_plugin) {
	msg("");
	print "[!] Start searching for plugin vulnerability? [y/N] ";
	$answer = strtolower( trim( fgets(STDIN) ) );
	if($answer == 'y') {
		$wpvuln = new WPVuln('plugin');
		$wpvuln->vuln($wpscan->list_plugins);
	}
}

msg("");
print "[!] Enumerate users? [y/N] ";
$answer = strtolower( trim( fgets(STDIN) ) );
if($answer == 'y') {
	$wpuser = new WPUser($wpscan->url);
	$userlist = $wpuser->enumerate();
	if(is_array($userlist)) {
		msg("");
		foreach ($userlist as $user) {
			msg("[+] {$user}");
		}
	} else {
		msg("[-] no user was found."); 
	}
}

msg("");
$end_time = time();
msg("[+] Finish Scan at " . date('d-m-Y h:iA', $end_time));
msg("[+] Total time taken is: " . round(($end_time - $start_time), 4) . " seconds");
?>
