<?php

date_default_timezone_set('Asia/Kuala_Lumpur');
define('ROOT_PATH', dirname(realpath(__FILE__)) );
define('DS', DIRECTORY_SEPARATOR);
define('LOG_FOLDER', 'logs');
define('Version', '3.1.0');



/*set_time_limit: 0 means infinite. Infinite is probably a bad idea as it may use up a lot of resources. 30 is default and might not be enough for enumerating themes and plugins. For more about set_time_limit read http://php.net/manual/en/function.set-time-limit.php*/
set_time_limit(0);


if( strtolower(php_sapi_name()) != 'cli' ) {
	define('isGUI', true);
}
else{
	define('isGUI', false);
}


if(isGUI && !isset($_POST['blankwindow'])){//Start form
	

	
?>
<html>
<head>
<style>
h2, h4, h6, a{
	font-family:Verdana, Geneva, sans-serif;
}
</style>
</head>
<body>
<form method="post">

<h2>Wordpress vulnerability scanner </h2>

<h6>Original by <a href="https://github.com/RamadhanAmizudin/" target="_blank">RamadhanAmizudin</a>. GUI added by <a href="https://github.com/NotANoob" target="_blank">NotANoob</a></h6>

<!--
I have commented out a few inputs because 
i haven't tested them with the gui version yet
You can uncomment and change them
if you want to test them.

From NotANoob
-->


<!--
<input type="checkbox" name="v">Check version
<input type="checkbox" name="upgrade">Update version<br><br>-->

Target URL <!--(e.g. "http://mywp.com/"):--> <input type="text" name="u" value="<?php echo (isset($_POST['u']) ? htmlspecialchars($_POST['u']) : 'http://localhost'); ?>"><br><br>

Options:<br>

<input type="checkbox" name="f"> Ignore if target is not wordpress.<br>

<input type="checkbox" name="wpvulndb"> Use WPVulnDB API Instead of local database. (Powered by wpvulndb.com API)<br>
<input type="checkbox" name="no-log">   Disable Logging<br><br>

<!--
Request:<br>
<input type="text" name="ua">    Set user-agent, default: random user agent<br>
<input type="text" name="t">     Numbers of threads, default: 10<br>
<input type="text" name="proxy"> Set proxy. eg: protocol://[username:password@]host:port<br><br>-->

Scanning:<br>
<input type="checkbox" name="d" checked>  Default scanning mode.<br>
<input type="checkbox" name="b" checked>  Show basic information about target.<!-- Eg: robots.txt path, check multisite, registration enable, readme file--><br>
<input type="checkbox" name="dp" checked> Discover plugin(s) via html source<br>
<input type="checkbox" name="dt" checked> Discover theme(s) via html source<br><br>

Plugin/Theme Enumeration:<br>
<input type="checkbox" name="ep"> Enumerate plugins<br>
<input type="checkbox" name="et"> Enumerate themes<br>
<input type="checkbox" name="vp"> Enumerate vulnerable plugins only<br>
<input type="checkbox" name="vt"> Enumerate vulnerable themes only<br><br>

User Enumeration:<br>
<input type="checkbox" name="eu"> Enumerate users<br>
<!--<input type="text" name="i" value="10">  Numbers of iteration. <br>-->
<input type="checkbox" name="f">  Enumerate through rss feeds, default: author pages<br>
<!--<input type="checkbox" name="B">  Set wordlist file(full path) to bruteforce username, default will use built-in wordlist<br>-->
<input type="checkbox" name="p">  Check if the site is protected before bruteforcing.
<br><br>

<!--
Bruteforce:<br>
<input type="checkbox" name="bf"> Bruteforce Mode<br>
<input type="checkbox" name="x">  Bruteforce through XMLRPC interface.<br><br>
<input type="checkbox" name="p">  Check if the site is protected before bruteforcing.<br>
<input type="text" name="U">  Set username or file containing user lists.<br>
<input type="checkbox" name="w">  Set wordlist file(full path), default will use built-in wordlist.<br><br>
-->


<!---->
<input type="checkbox" name="blankwindow"> Show output in blank window<br><br>


<input type="submit" name="submit" value="Submit"><br><br>

</form>
</body>
</html>
<?php
	
	if(!isset($_POST['submit'])){
		//This die() is to stop the rest of the script being loaded if the form has not yet been submitted and they're using gui. It looks bad but you should leave it here.
		die();
	}
	
	
}//End form


require_once(ROOT_PATH . '/base/load.php');


if(isGUI){
	$argv = $_POST;
}
else{
    $argv = parseArgs($argv);
}



Banner();
CheckRequirement();


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
    if(is_array($userlist)) {
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
