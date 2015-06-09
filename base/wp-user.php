<?php

class WPUser {

    var $url;

    function __construct($url) {
        $this->url = $url;
    }

    function enumerate() {
        if( Config::get('feed') ) {
            return $this->feed();
        } else {
            return $this->author();
        }
    }

    #enumerate user from author page
    private function author() {
        for($i = 1;; $i++) {
            $resp = HTTPRequest($this->url . '/?author=' . $i, false, null, false);
            if(stripos($resp, '200 ok') !== false) {
                preg_match('#<body class="archive author author-([^\s]+)#i', $resp, $match);
                preg_match('#/author/(.+?)/feed/#i', $resp, $match2);
                if( isset($match[1]) ) {
                    $users[] = $match[1];
                }
                if( isset($match2[1]) ) {
                    $users[] = $match2[1];
                }
                if( empty($users) ) {
                    break;
                }
            } elseif(stripos($resp, '301 moved permanently') !== false) {
                preg_match('#/author/([^/\b]+)/?#i', $resp, $match);
                if( isset($match[1]) ) {
                    $users[] = $match[1];
                }
                else {
                    break;
                }
            } else {
                break;
            }
        }
        return ( isset($users) ? array_unique($users) : false );
    }

    #enumerate user from feeds
    private function feed() {
        for($i = 1;; $i++) {
            $resp = HTTPRequest($this->url . '/?feed=rss2&paged=' . $i);
            if(stripos($resp, '200 ok') !== false) {
                preg_match_all('#<dc:creator><!\[CDATA\[(.+?)\]\]></dc:creator>#i', $resp, $match1);
                preg_match_all('#<dc:creator>(.+?)</dc:creator>#i', $resp, $match2);
                if( is_array($match1[1]) ) { 
                    foreach ($match1[1] as $user) {
                        $users[] = $user;
                    }
                }
                if( is_array($match2[1]) ) { 
                    foreach ($match2[1] as $user) {
                        $users[] = $user;
                    }
                }
                if( empty($users) ) {
                    break;
                }
            } else {
                break;
            }
        }
        return (isset($users) ? array_unique($users) : false);
    }
}
