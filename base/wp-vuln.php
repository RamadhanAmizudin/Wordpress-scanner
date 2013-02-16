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
class WPVuln {

	function plugin($plugins = array()) {
		foreach($plugins as $plugin) {
			$this->_plugin($plugin);
		}
	}
	
	protected function _plugin($plugin) {
		$data_path = ROOT_PATH . '/base/data/plugin-vuln.json';
		$data = json_decode(file_get_contents($data_path), true);
		foreach($data as $_plugin) {
			if(strtolower($_plugin['name']) == strtolower($plugin)) {
				foreach($_plugin['vulnerability'] as $vuln) {
					msg("");
					msg("[+] Found " . $vuln['title']);
					msg("[+] Reference:");
					if(is_array($vuln['reference'])) {
						foreach($vuln['reference'] as $ref) {
							msg("[*]\t" . $ref);
						}
					} else {
						msg("[*]\t" . $vuln['reference']);
					}
				}
			}
		}
	}

}