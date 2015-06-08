<?php

class WPEnum {

	var $total = 0;
	var $array = array();
	var $url, $type; 

	function __construct($url, $type) {
		$this->url = $url;
		$this->array = array_map('trim', file(ROOT_PATH . "/base/data/list-{$type}.txt"));
		$this->total = count($this->array);
		$this->type = $type;
	}
	
	function enumerate() {
		$return = false;
		print "[!] how many threads to use? [default = 10] ";
		$answer = trim(fgets(STDIN));
		$threads = ctype_digit($answer) ? $answer : 10;   
		foreach(array_chunk($this->array, $threads) as $chunk) {
			foreach ($chunk as $name) {
				$urls[] = $this->url . "/wp-content/{$this->type}/" . $name;
			}
			$respons = HTTPMultiRequest($urls, false);
			foreach ($respons as $key => $resp) {
				if(stripos($resp, '200 ok') !== false OR stripos($resp, '301 moved') !== false) {
				$return[] = $chunk[$key];
				msg("");
				msg("[!] Found {$chunk[$key]} theme.");
				msg("[*]     URL: http://wordpress.org/extend/{$this->type}/" . $chunk[$key] . "/");
				msg("[*]     SVN: http://{$this->type}.svn.wordpress.org/" . $chunk[$key] . "/");
				}
			}
			unset($urls);
		}
		return $return;
	}
}
