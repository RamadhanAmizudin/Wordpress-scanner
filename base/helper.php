<?php

// Some user agents can be found here http://techpatterns.com/downloads/firefox/useragentswitcher.xml (thx to Gianluca Brindisi)
function _user_agents() {
    $user_agents = array(
        // Windows
        "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/532.5 (KHTML, like Gecko) Chrome/4.0.249.0 Safari/532.5",
        "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/534.14 (KHTML, like Gecko) Chrome/9.0.601.0 Safari/534.14",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.27 (KHTML, like Gecko) Chrome/12.0.712.0 Safari/534.27",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.24 Safari/535.1",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; tr; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8 ( .NET CLR 3.5.30729; .NET4.0E)",
        "Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:7.0.1) Gecko/20100101 Firefox/7.0.1",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1092.0 Safari/536.6",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.1) Gecko/20100101 Firefox/10.0.1",
        "Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120403211507 Firefox/12.0",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:15.0) Gecko/20120427 Firefox/15.0a1",
        "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)",
        "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/6.0)",
        "Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00",
        "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",

        // MAC
        "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.15 Safari/534.13",
        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/418.8 (KHTML, like Gecko) Safari/419.3",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_0) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2; rv:10.0.1) Gecko/20100101 Firefox/10.0.1",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10",

        // Linux
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.20 Safari/535.1",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.24 (KHTML, like Gecko) Ubuntu/10.10 Chromium/12.0.703.0 Chrome/12.0.703.0 Safari/534.24",
        "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.9) Gecko/20100915 Gentoo Firefox/3.6.9",
        "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.16) Gecko/20120421 Gecko Firefox/11.0",
        "Mozilla/5.0 (X11; Linux i686; rv:12.0) Gecko/20100101 Firefox/12.0",
        "Opera/9.80 (X11; Linux x86_64; U; pl) Presto/2.7.62 Version/11.00",
        "Mozilla/5.0 (X11; U; Linux x86_64; us; rv:1.9.1.19) Gecko/20110430 shadowfox/7.0 (like Firefox/7.0");

    return $user_agents[ array_rand($user_agents) ];
}

/* default */
function _curl_options() {
    $opt = array(
        CURLOPT_HEADER => 1,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT => (Config::get('ua')) ? Config::get('ua') : _user_agents()
    );
    if(Config::get('proxy')) {
        $opt[CURLOPT_PROXY] = Config::get('proxy');
        // To do supply proxy auth information.
    }
    return $opt;
}

function HTTPRequest($url = '', $post = false, $postfield = '', $follow_redirection = true) {
    $options = _curl_options();
    if ( $follow_redirection ) {
        $options[CURLOPT_FOLLOWLOCATION] = 1;
    }
    if ( $post && $postfield != '' ) {
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $postfield;
    }

    $handle = curl_init($url);
    $ok = false;
    $data = "";

    if ( is_resource($handle) ) {
        if ( curl_setopt_array($handle, $options) != false ) {
            if ( ($data = curl_exec($handle)) != false ) {
                $ok = true;
            }
        }
    }
    curl_close($handle);

    return ( $ok ? $data : false );

}

function HTTPMultiRequest($urls = array(), $follow_redirection = true, $postData = false, $noBody = true) {
    $multiCurl = curl_multi_init();
    $options = _curl_options();
    if(!$postData && $noBody) {
        $options[CURLOPT_NOBODY] = 1;
    }
    if($postData) {
        $options[CURLOPT_POST] = 1;
    } else {
        // nawawi: this option will instruct curl to use GET
        $options[CURLOPT_CUSTOMREQUEST] = "GET";
    }
    if ( $follow_redirection ) {
        $options[CURLOPT_FOLLOWLOCATION] = 1;
    }

    foreach($urls as $i => $url) {
        $ch[$i] = curl_init($url);
        if($postData) {
            curl_setopt($ch[$i], CURLOPT_POSTFIELDS, $postData[$i][0]);
            curl_setopt($ch[$i], CURLOPT_HTTPHEADER, $postData[$i][1]);
        }
        curl_setopt_array($ch[$i], $options);
        curl_multi_add_handle($multiCurl, $ch[$i]);
    }

    do {
        usleep(10000); // dont flood
        curl_multi_exec($multiCurl, $active);
    } while($active > 0);

    // get data first
    foreach($ch as $id => $connection) {
        $data = curl_multi_getcontent($connection);
        $arrData[$id] = $data;
    }

    // and then we close
    foreach($ch as $id => $connection) {
        curl_multi_remove_handle($multiCurl, $connection);
        curl_close($connection);
    }
    curl_multi_close($multiCurl);

    return ( !empty($arrData) ? $arrData : false );
}

function msg($txt = "") {
    $text = sprintf("%s\n", $txt);
    if( !Config::get('nl') ) {
        write_log($text);
    }
    print $text;
}

/**
 * parseArgs Command Line Interface (CLI) utility function.
 * @author              Patrick Fisher <patrick@pwfisher.com>
 * @see                 https://github.com/pwfisher/CommandLine.php
 */
function parseArgs($argv = null) {
    $argv = $argv ? $argv : $_SERVER['argv']; array_shift($argv); $o = array();
    for ($i = 0, $j = count($argv); $i < $j; $i++) { $a = $argv[$i];
        if (substr($a, 0, 2) == '--') { $eq = strpos($a, '=');
            if ($eq !== false) { $o[substr($a, 2, $eq - 2)] = substr($a, $eq + 1); }
            else { $k = substr($a, 2);
                if ($i + 1 < $j && $argv[$i + 1][0] !== '-') { $o[$k] = $argv[$i + 1]; $i++; }
                else if (!isset($o[$k])) { $o[$k] = true; } } }
        else if (substr($a, 0, 1) == '-') {
            if (substr($a, 2, 1) == '=') { $o[substr($a, 1, 1)] = substr($a, 3); }
            else {
                foreach (str_split(substr($a, 1)) as $k) { if (!isset($o[$k])) { $o[$k] = true; } }
                if ($i + 1 < $j && $argv[$i + 1][0] !== '-') { $o[$k] = $argv[$i + 1]; $i++; } } }
        else { $o[] = $a; } }
    return $o;
}

function getLogFolder() {
    $url = parse_url( Config::get('url') );
    $path = '';
    if( isset( $url['path'] ) ) {
        $url['path'] = rtrim($url['path'], '/');
        $path = preg_replace('/[^a-z0-9]/i', '_', $url['path']);
    }
    $host_folder = $url['host'] . $path;
    $log_folder = ROOT_PATH . DS . LOG_FOLDER . DS . $host_folder;
    if( !is_dir( $log_folder ) ) {
        mkdir( $log_folder, 0777, true );
    }
    return $log_folder;
}

function write_log( $msg ) {
    $log_folder = getLogFolder();
    $fp = fopen($log_folder . DS . 'logs.txt', 'a');
    fwrite($fp, $msg);
    fclose($fp);
}

function write_vuln( $type, $array ) {
    $log_folder = getLogFolder();
    $fp = fopen($log_folder . DS . $type . '-vuln.json', 'w');
    fwrite($fp, json_encode( $array, JSON_PRETTY_PRINT ));
    fclose($fp);
}

function write_info( $type, $array ) {
    $log_folder = getLogFolder();
    $fp = fopen($log_folder . DS . $type . '.json', 'w');
    fwrite($fp, json_encode( $array, JSON_PRETTY_PRINT ));
    fclose($fp);
}
