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
