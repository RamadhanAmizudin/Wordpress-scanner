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
class WPVuln {

	var $data_path;
	var $type;
	var $found;

	function __construct($type) {
		$this->data_path = ROOT_PATH . "/base/data/{$type}-vuln.json";
		$this->type = $type;
		$this->found = 0;
	}

	function vuln($var) {
		$data = json_decode(file_get_contents($this->data_path), true);
		foreach((array)$var as $vuln) {
			if(isset($data[$vuln["{$this->type}_name"]])) {
                $found = $data[$vuln[$this->type."_name"]]['vulnerability']['title'];
                $ref = $data[$vuln[$this->type."_name"]]['vulnerability']['references'];
                if ( $found != '' && $ref != '' ) {
				    msg("");
				    msg("[+] Found " . $data[$vuln["{$this->type}_name"]]['vulnerability']['title']);
				    $this->reference($data[$vuln["{$this->type}_name"]]['vulnerability']['references']);
				    $this->found++;
                }
			}
		}
		$this->found ?: msg("[-] No vulnerability was found");
	}

	function version($version) {
		$data = json_decode(file_get_contents($this->data_path), true);
		if(isset($data[$version])) {
			msg("");
			msg("[+] Found " . $data[$version]['vulnerability']['title']);
			$this->reference($data[$version]['vulnerability']['references']);
			$this->found++;
		}
		$this->found ?: msg("[-] No vulnerability was found");
	}

	function reference($ref) {
		msg("[+] Reference:");
		foreach((array)$ref as $key => $reff) {
			if($key == 'metasploit') {
				foreach ((array)$reff as $id) {
					msg("[*]\thttp://www.metasploit.com/modules/" . $id);
				}
			} elseif($key == 'cve') {
				foreach ((array)$reff as $id) {
					msg("[*]\thttp://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-" . $id);
				}
			} elseif($key == 'osvdb') {
				foreach ((array)$reff as $id) {
					msg("[*]\thttp://osvdb.org/" . $id);
				}
			} elseif($key == 'secunia') {
				foreach ((array)$reff as $id) {
					msg("[*]\thttp://secunia.com/advisories/" . $id);
				}
			} elseif($key == 'exploitdb') {
				foreach ((array)$reff as $id) {
					msg("[*]\thttp://www.exploit-db.com/exploits/" . $id);
				}
			} elseif($key == 'url') {
				foreach ((array)$reff as $url) {
					msg("[*]\t" . $url);
				}
			}
		}
	}
}
