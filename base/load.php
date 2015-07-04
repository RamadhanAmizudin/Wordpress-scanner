<?php

defined('ROOT_PATH') or die();

require ROOT_PATH . '/base/config.class.php';
require ROOT_PATH . '/base/helper.php';
require ROOT_PATH . '/base/requirement.php';
require ROOT_PATH . '/base/update.php';
require ROOT_PATH . '/base/upgrade.php';
require ROOT_PATH . '/base/wp-version.php';
require ROOT_PATH . '/base/wp-vuln.php';
require ROOT_PATH . '/base/wp-user.php';
require ROOT_PATH . '/base/wp-brute.php';
require ROOT_PATH . '/base/wp-enum.php';

function Banner() {
$text = "
+----------------------------------------------------+
|  __    __              _                           |
| / / /\ \ \___  _ __ __| |_ __  _ __ ___  ___ ___   |
| \ \/  \/ / _ \| '__/ _` | '_ \| '__/ _ \/ __/ __|  |
|  \  /\  / (_) | | | (_| | |_) | | |  __/\__ \__ \  |
|   \/  \/ \___/|_|  \__,_| .__/|_|  \___||___/___/  |
|                         |_|                        |
|                      Vulnerability Scanner v".Version."  |
+----------------------------------------------------+
";
echo $text;
}

function Help() {
    Config::set('nl', true);
    msg("Usage: php app.php [options]");
    msg("Guidelines: https://www.owasp.org/index.php/OWASP_Wordpress_Security_Implementation_Guideline");
    msg("");
    msg("Options:");
    msg("\t-h,   --help\t\t\tShow this help message.");
    msg("\t-u,   --url\t\t\tTarget URL (e.g. \"http://mywp.com/\")");
    msg("\t-f,   --force\t\t\tIgnore if target is not wordpress.");
    msg("\t-v,   --version\t\t\tCheck for available version");
    msg("\t--upgrade\t\t\tUpgrade to newer version");
    msg("\t--wpvulndb\t\t\tUse WPVulnDB API Instead of local database. (Powered by wpvulndb.com API)");
    msg("\t--no-log\t\t\tDisable Logging");
    msg("");
    msg("Request:");
    msg("\t--ua, --user-agent\t\tSet user-agent, default: random user agent");
    msg("\t-t,   --thread\t\t\tnumbers of threads, default: 10");
    msg("\t--proxy\t\t\t\tSet proxy. eg: protocol://[username:password@]host:port");
    msg('');
    msg("Scanning:");
    msg("\t-d,   --default\t\t\tDefault scanning mode");
    msg("\t\t\t\t\tEquivalent to --dp,--dt,--b option");
    msg("\t-b,   --basic\t\t\tShow basic information about target");
    msg("\t\t\t\t\tEg: robots.txt path, check multisite, registration enable, readme file");
    msg("\t--dp, --discover-plugin\t\tDiscover plugin(s) via html source");
    msg("\t--dt, --discover-theme\t\tDiscover theme(s) via html source");
    msg('');
    msg('Plugin/Theme Enumeration:');
    msg("\t--ep, --enumerate-plugin\tEnumerate plugins");
    msg("\t--et, --enumerate-theme\t\tEnumerate themes");
    msg("\t--vp, --vuln-plugin\t\tEnumerate vulnerable plugins only");
    msg("\t--vt, --vuln-theme\t\tEnumerate vulnerable themes only");
    msg('');
    msg('User Enumeration:');
    msg("\t--eu, --enumerate-user\t\tEnumerate users");
    msg("\t-i,   --iterate\t\t\tnumbers of iteration, default: 10");
    msg("\t-f,   --feed\t\t\tEnumerate through rss feeds, default: author pages");
    msg("\t-B,   --ubrute\t\t\tSet wordlist file(full path) to bruteforce username, default will use built-in wordlist");
    msg("\t-p,   --protect\t\t\tCheck if the site is protected before bruteforcing, use with -B or --ubrute");
    msg('');
    msg('Bruteforce:');
    msg("\t--bf, --bruteforce\t\tBruteforce Mode");
    msg("\t-x,   --xmlrpc\t\t\tBruteforce through XMLRPC interface");
    msg("\t-p,   --protect\t\t\tCheck if the site is protected before bruteforcing");
    msg("\t-F,   --ufound\t\t\tSet username to enumerated users");
    msg("\t-U,   --user\t\t\tSet username or file containing user lists");
    msg("\t-w,   --wordlist\t\tSet wordlist file(full path), default will use built-in wordlist");
    msg();
}

function NoOption() {
    Config::set('nl', true);
    msg("");
    msg("[!] Usage php app.php [options]");
    msg("[!] app.php: error: missing a mandatory option, use -h or --help for help");
    msg("");
    exit;
}

class WPScan {

    var $url, $wp_path, $xmlrpc_path, $rss_path, $robots_path, $readme_path, $theme_name, $sdb_path;
    var $is_multisite, $registration_enabled, $list_plugins, $fpd_path;
    var $wp_content_path = 'wp-content';
    var $plugin_path = 'plugins';
    protected $homepage_sc;

    function __construct($host) {
        $this->url = $this->new_url( rtrim( $host, '/' ) );
    }

    function get_version() {
        $wpversion = new WPVersion($this->url, $this->homepage_sc);
        return $wpversion->get_version();
    }

    function is_wordpress() {
        $this->homepage_sc = HTTPRequest($this->url);
        preg_match('/x-pingback: (.+)/i', $this->homepage_sc, $xmlrpc);
        $this->xmlrpc_path = (isset($xmlrpc[1])) ? trim($xmlrpc[1]) : false;
        if(preg_match('#wp-content#i', $this->homepage_sc)) {
            return true;
        } else {
            $resp = HTTPRequest($this->xmlrpc_path);
            if(preg_match('#XML-RPC server accepts POST requests only#i', $resp)) {
                return true;
            }
        }
        return false;
    }

    function search_plugins() {
        $data_path = ROOT_PATH . '/base/data/list-plugins.txt';
        $data = array_map('trim', file($data_path));
        preg_match_all("/wp-content\/plugins\/(.*?)(\/|'|\")/i", $this->homepage_sc, $match);
        //caching plugins from header
        preg_match('/x-powered-by: w3 total cache\/[0-9.]+/i', $this->homepage_sc, $w3tc_header);
        preg_match('/wp-super-cache: served supercache/i', $this->homepage_sc, $supercache_header);
        //caching && seo plugins from content
        preg_match('/<!-- Performance optimized by W3 Total Cache/i', $this->homepage_sc, $w3tc_content);
        preg_match('/<!-- Cached page generated by WP-Super-Cache/i', $this->homepage_sc, $supercache_content);
        preg_match('/<!-- all in one seo pack/i', $this->homepage_sc, $AIO_SEO);
        preg_match('/<!-- This site is optimized with the Yoast WordPress SEO plugin/i', $this->homepage_sc, $Yoast_SEO);
        if( isset($w3tc_header[0]) OR isset($w3tc_content[0]) )
            $match[1][] = 'w3-total-cache';
        if( isset($supercache_header[0]) OR isset($supercache_content[0]) )
            $match[1][] = 'wp-super-cache';
        if( isset($AIO_SEO[0]) )
            $match[1][] = 'all-in-one-seo-pack';
        if( isset($Yoast_SEO[0]) )
            $match[1][] = 'wordpress-seo';
        $plugins = array_unique($match[1]);
        foreach($plugins as $plugin) {
            msg('');
            msg("[+] Found {$plugin} plugin.");
            if(in_array($plugin, $data)) {
                msg("[!] Plugin URL: http://wordpress.org/extend/plugins/" . $plugin . "/");
                msg("[!] Plugin SVN: http://plugins.svn.wordpress.org/" . $plugin . "/");
            }
        }
        $this->list_plugins = !empty($plugins) ? $plugins : false;
    }

    function parser() {
        if( Config::get('default') OR Config::get('dt') ) {
            preg_match('#themes/(.*?)/style.css#i', $this->homepage_sc, $theme);
            $this->theme_name = (isset($theme[1])) ? trim($theme[1]) : false;
        }
        if( Config::get('default') OR Config::get('basic') ) {
            preg_match('#<link .* type="application/rss\+xml" .* href="([^"]+)" />#i', $this->homepage_sc, $rss);
            $this->rss_path = (isset($rss[1])) ? trim($rss[1]) : false;
            $this->robots_path = (preg_match('/200 ok/i', HTTPRequest($this->url.'/robots.txt'))) ? $this->url.'/robots.txt' : false;
            $this->readme_path = (preg_match('/200 ok/i', HTTPRequest($this->url.'/readme.html'))) ? $this->url.'/readme.html' : false;
            $this->sdb_path = $this->__search_sdb();
            $this->__is_multisite();
            $this->__registration_enabled();
	    $this->__fpd();
        }
    }

    private function __fpd() {
        $this->fpd_path = $this->url . '/wp-includes/rss-functions.php';
        $response = HTTPRequest($this->fpd_path);
        if(stripos($response, '_deprecated_file()') === false) {
            $this->fpd_path = false;
        }
    }

    private function __registration_enabled() {
        $path = ($this->is_multisite) ? '/wp-signup.php' : '/wp-login.php?action=register';
        $response = HTTPRequest($this->url . $path, false, '', false);
        if(stripos($response, '302 Found')) {
            $this->registration_enabled = false;
        } elseif(preg_match('/<form id="setupform" method="post" action="[^"]*wp-signup\.php[^"]*">/i', $response)) {
            $this->registration_enabled = true;
        } elseif(preg_match('/<form name="registerform" id="registerform" action="[^"]*wp-login\.php[^"]*"/i', $response)) {
            $this->registration_enabled = true;
        } else {
            $this->registration_enabled = false;
        }
    }

    private function __is_multisite() {
        $response = HTTPRequest($this->url . '/wp-signup.php', false, '', false);
        $headers = explode("\r\n", $response);
        foreach($headers as $header) {
            if(stripos($header, 'location:') === 0) {
                if(preg_match('/wp-login\.php\?action=register/i', $header)) {
                    $this->is_multisite =  false;
                } elseif(preg_match('/wp-signup\.php/i', $header)) {
                    $this->is_multisite =  true;
                }
            }
            if(stripos($header, 'HTTP/1.1 200 OK') !== false) {
                $this->is_multisite =  true;
            }
        }
        $this->is_multisite =  false;
    }

    private function __search_sdb() {
        $files = array('sdb.php', 'searchreplacedb2.php');
        foreach($files as $file) {
            $response = HTTPRequest($this->url . '/' . $file);
            if(stripos($response, 'by interconnect') !== false) {
                return $this->url . '/' . $file;
            }
        }
        return false;
    }

    private function new_url($current) {
        $response = HTTPRequest($current, false, '', false);
        $headers = explode("\r\n", $response);
        foreach($headers as $header) {
            if(stripos($header, 'location:') === 0) {
                return rtrim( ltrim( str_ireplace('location:', '', $header) ), '/' );
            }
        }
        return $current;
    }
}
