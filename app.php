<?php

date_default_timezone_set('Asia/Kuala_Lumpur');
define('ROOT_PATH', dirname(realpath(__FILE__)) );
define('DS', DIRECTORY_SEPARATOR);
define('LOG_FOLDER', 'logs');
define('Version', '3.1.0');

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

if( Config::get('version') ) {
    check_version();
    exit;
}

if( Config::get('upgrade') ) {
    download();
    exit();
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

$e_plugins = false;
$e_themes = false;
$plugins = false;
$themes = false;
$info = [];

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
    $info['version'] = $version;
    msg(vsprintf("[+] Wordpress Version %s, using %s method", $version));
    msg("");
    msg("[+] Finding version vulnerability");
    $wpvuln = new WPVuln('version');
    $wpvuln->vuln($version['version']);
    msg("");
}

if( Config::get('default') OR Config::get('basic') ) {
    if($wpscan->robots_path) {
        $info['robots_path'] = $wpscan->robots_path;
        msg("[+] robots.txt available at " . $wpscan->robots_path);
    }
    if($wpscan->readme_path) {
        $info['readme_path'] = $wpscan->readme_path;
        msg("[+] Wordpress Readme file at " . $wpscan->readme_path);
    }
    if($wpscan->sdb_path) {
        $info['sdb_path'] = $wpscan->sdb_path;
        msg("[+] Found script for database replacing: " . $wpscan->sdb_path);
    }
    if($wpscan->is_multisite) {
        $info['is_multisite'] = $wpscan->is_multisite;
        msg("[+] Multi-site enabled (http://codex.wordpress.org/Glossary#Multisite)");
    }
    if($wpscan->registration_enabled) {
        $info['registration_enabled'] = $wpscan->registration_enabled;
        msg("[+] Registration enabled! ");
    }
    if($wpscan->xmlrpc_path) {
        $info['xmlrpc_path'] = $wpscan->xmlrpc_path;
        msg("[+] XML-RPC Interface available under " . $wpscan->xmlrpc_path);
    }
    if($wpscan->fpd_path) {
        $info['fpd_path'] = $wpscan->fpd_path;
        msg("[+] Full Path Disclosure (FPD) available at : " . $wpscan->fpd_path);
    }
}

if( Config::get('default') OR Config::get('dt') ) {
    if($wpscan->theme_name) {
        msg("[+] Target is using {$wpscan->theme_name} theme");
        $themes[] = $wpscan->theme_name;
    }
}

if( Config::get('et') OR Config::get('vuln-theme') ) {
    msg("");
    msg('[+] Enumerating themes');
    msg("[!] Warning: This may take a while!");
    $wptheme = new WPEnum($wpscan->url, 'themes');
    msg("[!] Total {$wptheme->total} themes!");
    $e_themes = $wptheme->enumerate();
    if($e_themes AND $wpscan->theme_name) {
        $themes = array_unique(array_merge((array)$wpscan->theme_name, $e_themes));
    } elseif($e_themes) {
        $themes = $e_themes;
    }
}

if($themes) {
    $info['themes'] = $themes;
    msg("");
    msg("[+] Finding theme vulnerability");
    $wpvuln = new WPVuln('theme');
    $wpvuln->vuln($themes);
} else {
    msg("[-] No theme was found");
}

if( Config::get('dp') OR Config::get('default') ) {
    msg("");
    msg("[+] Looking for visible plugins on homepage");
    $wpscan->search_plugins();
    if($wpscan->list_plugins) {
        $plugins[] = $wpscan->list_plugins;
    }
}

if( Config::get('ep') OR Config::get('vuln-plugin') ) {
    msg('');
    msg('[+] Enumerating Plugins');
    msg("[!] Warning: This may take a while!");
    $wpplugin = new WPEnum($wpscan->url, 'plugins');
    msg("[!] Total {$wpplugin->total} plugins!");
    $e_plugins = $wpplugin->enumerate();
    if($e_plugins AND $wpscan->list_plugins) {
        $plugins = array_unique(array_merge((array)$wpscan->list_plugins, $e_plugins));
    } elseif($e_plugins) {
        $plugins = $e_plugins;
    }
}

if($plugins) {
    $info['plugins'] = $plugins;
    msg("");
    msg("[+] Finding plugin vulnerability");
    $wpvuln = new WPVuln('plugin');
    $wpvuln->vuln($plugins);
} else {
    msg("[-] No plugin was found");
}

if( Config::get('eu') ) {
    msg("");
    msg("[+] Enumerating Users");
    $wpuser = new WPUser($wpscan->url);
    $userlist = $wpuser->enumerate();
    if($userlist) {
        $info['users'] = $userlist;
        foreach ($userlist as $user) {
            msg("[+] {$user}");
        }
    } else {
        msg("[-] No user was found");
    }
}

if( Config::get('bf') ) {
    msg("");
    msg("[+] Bruteforcing");
    if( Config::get('xmlrpc') ) {
        $method = $wpscan->xmlrpc_path ? 'xmlrpc' : 0;
    } else {
        $method = 'wp-login';
    }
    if($method) {
        $brute = new WPBrute($wpscan->url);
        if( Config::get('ufound') ) {
            $brute->usernames = false;
            if( Config::get('eu') ) {
                $brute->usernames = $userlist;
            }
        }
        $logins = $brute->brute($method);
        if($logins) {
    	    if( !Config::get('nl') ) {
    	        write_info('credentials', $logins);
    	    }
            foreach ($logins as $cred) {
                msg("[!] ".$cred[0].":".$cred[1]);
            }
        }
    } else {
        msg("[-] XMLRPC interface is not available");
    }
}

if( !Config::get('nl') ) {
    write_info('site-information', $info);
}

$end_time = time();
msg("");
msg("[+] Finish Scan at " . date('d-m-Y h:iA', $end_time));
msg("[+] Total time taken is: " . round(($end_time - $start_time), 4) . " seconds");
msg("");
