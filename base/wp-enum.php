<?php

class WPEnum {

    var $total = 0;
    var $array = array();
    var $url, $type;

    function __construct($url, $type) {
        $this->url = $url;
        if( Config::get('vuln-plugin') || Config::get('vuln-theme') ) {
            $path = ROOT_PATH . '/base/data/'.rtrim($type, 's').'-vuln.json';
            $this->array = array_map(function($a) { return key($a); }, json_decode(file_get_contents($path), 1));
        } else {
            $this->array = array_map('trim', file(ROOT_PATH . "/base/data/list-{$type}.txt"));
        }
        $this->total = count($this->array);
        $this->type = $type;
    }

    function enumerate() {
        $return = false;
        $str = rtrim($this->type, 's');
        if( Config::get('thread') ) {
            $threads = Config::get('thread');
        } else {
            $threads = 10;
        }
        foreach(array_chunk($this->array, $threads) as $chunk) {
            foreach ($chunk as $name) {
                $urls[] = $this->url . "/wp-content/{$this->type}/" . $name;
            }
            $respons = HTTPMultiRequest($urls, false);
            foreach ($respons as $key => $resp) {
                if(stripos($resp, '200 ok') !== false OR stripos($resp, '301 moved') !== false) {
                $return[] = $chunk[$key];
                msg("");
                msg("[!] Found {$chunk[$key]} {$str}");
                msg("[*]     URL: http://wordpress.org/extend/{$this->type}/" . $chunk[$key] . "/");
                msg("[*]     SVN: http://{$this->type}.svn.wordpress.org/" . $chunk[$key] . "/");
                }
            }
            unset($urls);
        }
        return $return;
    }
}
