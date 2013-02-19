<?php
/**
	A Wordpress Scaner
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
		$data_path = ROOT_PATH . '/base/data/list-plugins.txt';
		$this->url = $url;
		$this->a_plugin = file($data_path);
		$this->total_plugins = count($this->a_plugin);
	}
	
	function enumerate() {
		$plugins = false;
		foreach($this->a_plugin as $plugin) {
			// yeah lot of slow single-thread request!
			$resp = HTTPRequest($this->url . '/wp-content/plugins/' . $plugin);
			if(stripos($resp, '200 ok') !== false) {
				$plugins[] = array('plugin_name' => $plugin,
								   'url' => 'http://wordpress.org/extend/plugins/'.$plugin.'/',
								   'svn' => 'http://svn.wp-plugins.org/' . $plugin . '/');
								   
				msg("[+] Found {$plugin} plugin.");
				msg("[!] Plugin URL: http://wordpress.org/extend/plugins/" . $plugin . "/");
				msg("[!] Plugin SVN: http://svn.wp-plugins.org/" . $plugin . "/");
			}
		}
		return $plugins;
	}
}