<?php

date_default_timezone_set('Asia/Kuala_Lumpur');
define('ROOT_PATH', dirname(realpath(__FILE__)) );
define('Version', '3.0.0');

if( strtolower(php_sapi_name()) != 'cli' ) {
    printf("%s\n", "Please run only from command line interface.");
    exit;
}

require_once(ROOT_PATH . '/base/load.php');

Banner();
CheckRequirement();

$argv = parseArgs($argv);
Config::handle($argv);

if( Config::get('help') ) {
    Help();
    exit;
}

// credit syahiran
$ok = false;
$keys = array_keys( Config::all() );
foreach( $keys as $key ) {
    switch($key) {
        case 'default':
        case 'basic':
        case 'et':
        case 'ep':
        case 'dt':
        case 'dp':
        case 'bf':
        case 'eu':
                $ok = true;
            break;
    }
}

if( empty($argv) OR !$ok OR !Config::get('url')) {
    NoOption();
}

$found_plugin = false;
$found_theme = false;
$e_plugins = false;
$found_e_plugin = false;
$theme = array();

$wpscan = new WPScan( Config::get('url') );
msg("[+] Target: " . $wpscan->url);
$start_time = time();
msg("[+] Start Time: " . date('d-m-Y h:iA', $start_time));

if( !$wpscan->is_wordpress() ) {
    msg("[-] This site does not seem to be running WordPress!");
    if( !Config::get('force') ) {
        exit;
    }
}

$wpscan->parser();
$version = $wpscan->get_version();

if( $version ) {
    msg(vsprintf("[+] Wordpress Version %s, using %s method", $version));
    $wpvuln = new WPVuln('version');
    $wpvuln->version($version['version']);
    msg("");
}

if( Config::get('default') OR Config::get('basic') ) {
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
}
if( Config::get('default') OR Config::get('dt') ) {
    if($wpscan->theme_name) {
        msg("[+] Target is using {$wpscan->theme_name} theme");
        $theme[] = array('theme_name' => $wpscan->theme_name); 
    }
}

if( Config::get('et') ) {
    msg("");
    msg('[+] Enumerating Themes');
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

if( !empty($theme) ) {
    msg("");
    msg("[+] Finding Theme Vulnerability");

    $wpvuln = new WPVuln('theme');
    $wpvuln->vuln($theme);
}

if( Config::get('dp') OR Config::get('default') ) {
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
}

if( Config::get('ep') ) {
    msg('[+] Enumerating Plugins');
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
    msg("[+] Finding Plugin Vulnerability");
    $wpvuln = new WPVuln('plugin');
    $wpvuln->vuln($wpscan->list_plugins);
}

if( Config::get('eu') ) {
    msg("[+] Enumerating Users");
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

$end_time = time();
msg("[+] Finish Scan at " . date('d-m-Y h:iA', $end_time));
msg("[+] Total time taken is: " . round(($end_time - $start_time), 4) . " seconds");
msg("");
