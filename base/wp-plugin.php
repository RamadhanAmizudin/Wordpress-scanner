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

class WPPlugin {

	var $total_plugins = 0;
	var $a_plugin = array();
	var $url; 

	function __construct($url) {
		$this->url = $url;
		$this->a_plugin = file(ROOT_PATH . '/base/data/list-plugins.txt');
		$this->total_plugins = count($this->a_plugin);
	}
	
	function enumerate() {
		$plugins = false;
		$start = 0;
		print "[!] how many threads to use? [default = 10] ";
		$answer = trim(fgets(STDIN));
		$threads = ctype_digit($answer) ? $answer : 10;   
		foreach(array_chunk($this->a_plugin, $threads) as $pluginsChunk) {
			progress_bar($start, $this->total_plugins);
			foreach ($pluginsChunk as $pluginName) {
				$urls[] = $this->url . '/wp-content/plugins/' . $pluginName;
			}
			$respons = HTTPMultiRequest($urls, false);
			foreach ($respons as $key => $resp) {
				if(stripos($resp, '200 ok') !== false) {
				$plugins[] = array('plugin_name' => $pluginsChunk[$key],
								   'url' => 'http://wordpress.org/extend/plugins/'. $pluginsChunk[$key] .'/',
								   'svn' => 'http://plugins.svn.wordpress.org/' . $pluginsChunk[$key] . '/');
								   
				msg("[+] Found {$pluginsChunk[$key]} plugin.");
				msg("[!] Plugin URL: http://wordpress.org/extend/plugins/" . $pluginsChunk[$key] . "/");
				msg("[!] Plugin SVN: http://plugins.svn.wordpress.org/" . $pluginsChunk[$key] . "/");
				}
			}
			$start += count($pluginsChunk);
		}
		return $plugins;
	}
}
