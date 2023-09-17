<?php

date_default_timezone_set('Asia/Kuala_Lumpur');
define('ROOT_PATH', dirname(realpath(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);
define('LOG_FOLDER', 'logs');
define('Version', '3.1.0');

if (strtolower(php_sapi_name()) != 'cli') {
    fprintf(STDERR, "Please run only from the command line interface.\n");
    exit(1);
}
// credits to the original owner
require_once(ROOT_PATH . '/base/load.php');

Banner();
CheckRequirement();

$argv = parseArgs($argv);

Config::handle($argv);

if (Config::get('help')) {
    Help();
    exit;
}

if (Config::get('version')) {
    check_version();
    exit;
}

if (Config::get('upgrade')) {
    download();
    exit();
}

$ok = false;
$allowedKeys = ['default', 'basic', 'et', 'ep', 'dt', 'dp', 'bf', 'eu'];
$keys = array_keys(Config::all());
foreach ($keys as $key) {
    if (in_array($key, $allowedKeys)) {
        $ok = true;
        break;
    }
}

if (empty($argv) || !$ok || !Config::get('url')) {
    NoOption();
}

$e_plugins = false;
$e_themes = false;
$plugins = false;
$themes = false;
$info = [];

$wpscan = new WPScan(Config::get('url'));
msg("[+] Target: " . $wpscan->url);
$start_time = time();
msg("[+] Start Time: " . date('d-m-Y h:iA', $start_time));

if (!$wpscan->is_wordpress()) {
    msg("[-] This site does not seem to be running WordPress!");
    if (!Config::get('force')) {
        exit;
    }
}

$wpscan->parser();
$version = $wpscan->get_version();

if ($version) {
    $info['version'] = $version;
    msg(vsprintf("[+] Wordpress Version %s, using %s method", $version));
    msg("");
    msg("[+] Finding version vulnerability");
    $wpvuln = new WPVuln('version');
    $wpvuln->vuln($version['version']);
    msg("");
}

if (Config::get('default') || Config::get('basic')) {
    if ($wpscan->robots_path) {
        $info['robots_path'] = $wpscan->robots_path;
        msg("[+] robots.txt available at " . $wpscan->robots_path);
    }
}


function handleErrors($errno, $errstr, $errfile, $errline)
{
    fprintf(STDERR, "Error: [%d] %s in %s on line %d\n", $errno, $errstr, $errfile, $errline);
}

set_error_handler("handleErrors");

try {
} catch (Exception $e) {
    fprintf(STDERR, "An error occurred: %s\n", $e->getMessage());
    exit(1);
}

$end_time = time();
msg("");
msg("[+] Finish Scan at " . date('d-m-Y h:iA', $end_time));
msg("[+] Total time taken is: " . round(($end_time - $start_time), 4) . " seconds");
msg("");
