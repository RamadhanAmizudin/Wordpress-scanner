<?php

class WPVuln {

	var $type;
	var $found;
	var $data;

	function __construct($type) {
		$this->type = $type;
		$this->found = 0;
		$this->data = json_decode(file_get_contents(ROOT_PATH . "/base/data/{$type}-vuln.json"), true);
	}

	function vuln($var) {
		foreach ($this->data as $vuln) {
			if( in_array(key($vuln), (array)$var) ) {
				$this->output($vuln[key($vuln)]);
			}
		}
		$this->found ?: msg("[-] No vulnerability was found");
	}

	function output($array) {
		foreach ($array['vulnerabilities'] as $vuln) {
			msg("");
			msg("[!] " . $vuln['title']);
			if( isset($vuln['fixed_in']) ) {
				msg("[+] Fixed in version " . $vuln['fixed_in']);
			}
			$this->reference($vuln);
			$this->found++;
		}
	}

	function reference($ref) {
		msg("[+] Reference:");
		foreach((array)$ref as $key => $reff) {
			if($key == 'id') {
				foreach ((array)$reff as $id) {
					msg("[*]\thttp://wpvulndb.com/vulnerabilities/" . $id);
				}
			} elseif($key == 'metasploit') {
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
