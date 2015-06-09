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
		if( Config::get('wpvulndb') ) {
			$this->wpvulndb($var);
		} else {
			foreach ($this->data as $vuln) {
				if( in_array(key($vuln), (array)$var) ) {
					$this->output($vuln[key($vuln)]);
				}
			}
		}
		$this->found ?: msg("[-] No vulnerability was found");
	}

	function wpvulndb($vars) {
		switch($this->type) {
			case 'theme':
			case 'plugin':
					$url = "https://wpvulndb.com/api/v1/{$this->type}s/";
				break;

			case 'version':
					$url = "https://wpvulndb.com/api/v1/wordpresses/";
				break;

			default:
					msg("[!] Unsupported type: {$this->type}");
					return false;
				break;
		}
		foreach((array)$vars as $var) {
			foreach((array)$var as $v) {
				$v = str_ireplace('.', '', $v);
				$url .= $v;
				$resp = HTTPRequest($url);
				if( stripos( $resp, 'The page you were looking for doesn\'t exist (404)') === false ) {
					$resp = explode("\r\n\r\n", $resp);
					$resp = $resp[1];
					$this->output( json_decode( $resp, true ) );
				}
			}
		}
		return true;
	}

	function output($array) {
		if( Config::get('wpvulndb') ) {
			$array['vulnerabilities'] = ($this->type == 'version') ? $array['wordpress']['vulnerabilities'] : $array[$this->type]['vulnerabilities'];
		}

		if( !Config::get('nl') ) {
			write_vuln($this->type, $array['vulnerabilities']);
		}

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
