<?php

class WPUser {

    var $url;
    var $users = array();

    function __construct($url) {
        $this->url = $url;
    }

    function enumerate() {
        for($i = 1;; $i++) {
            $resp = HTTPRequest($this->url . '/?author=' . $i, null, null, 0);
            if(stripos($resp, '301 moved permanently') !== false) {
                preg_match('/\/author\/([^\/\b]+)\/?/i', $resp, $match);
                if(isset($match[1])) $users[] = $match[1];
                else break;
            } elseif(stripos($resp, '200 ok') !== false) {
                preg_match('/<body class="archive author author\-(.*?) author">/si', $resp, $match);
                if(isset($match[1])) $users[] = $match[1];
                else break;
            } else break;
        }
        if(!empty($users)) return $users;
        else return false;
    }

}
