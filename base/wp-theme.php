<?php

class WPTheme {

	var $total_themes = 0;
	var $a_theme = array();
	var $url; 

	function __construct($url) {
		$this->url = $url;
		$this->a_theme = file(ROOT_PATH . '/base/data/list-themes.txt');
		$this->total_themes = count($this->a_theme);
	}
	
	function enumerate() {
		$themes = false;
		$start = 0;
		print "[!] how many threads to use? [default = 10] ";
		$answer = trim(fgets(STDIN));
		$threads = ctype_digit($answer) ? $answer : 10;   
		foreach(array_chunk($this->a_theme, $threads) as $themesChunk) {
			progress_bar($start, $this->total_themes);
			foreach ($themesChunk as $themeName) {
				$urls[] = $this->url . '/wp-content/themes/' . $themeName;
			}
			$respons = HTTPMultiRequest($urls, false);
			foreach ($respons as $key => $resp) {
				if(stripos($resp, '200 ok') !== false) {
				$themes[] = array('theme_name' => $themesChunk[$key],
								   'url' => 'http://wordpress.org/extend/themes/'. $themesChunk[$key] .'/',
								   'svn' => 'http://themes.svn.wordpress.org/' . $themesChunk[$key] . '/');
								   
				msg("[+] Found {$themesChunk[$key]} theme.");
				msg("[!] theme URL: http://wordpress.org/extend/themes/" . $themesChunk[$key] . "/");
				msg("[!] theme SVN: http://themes.svn.wordpress.org/" . $themesChunk[$key] . "/");
				}
			}
			$start += count($themesChunk);
		}
		return $themes;
	}
}
