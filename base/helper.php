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

// Some user_agents can be found there http://techpatterns.com/downloads/firefox/useragentswitcher.xml (thx to Gianluca Brindisi)
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
    return array(
        CURLOPT_HEADER => 1,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT => _user_agents()
    );
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

function HTTPMultiRequest($urls = array(), $follow_redirection = true) {
    //no support for GET request, yet!

    $multiCurl = curl_multi_init();
    $options = _curl_options();

    $options[CURLOPT_NOBODY] = 1;
    // nawawi: this option will instruct curl to use GET
    $options[CURLOPT_CUSTOMREQUEST] = "GET";

    if ( $follow_redirection ) {
        $options[CURLOPT_FOLLOWLOCATION] = 1;
    }

    foreach($urls as $i => $url) {
        $ch[$i] = curl_init($url);
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

function msg($txt) {
	printf("%s\n", $txt);
}

function progress_bar($done, $total) {
    $percentage = (double)($done/$total);
    printf("\r[!] Progress: %d/%d - %d%%", $done, $total, number_format($percentage*100, 0));
    flush();
}
?>
