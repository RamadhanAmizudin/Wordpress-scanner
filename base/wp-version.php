<?php
// To Do
// Tambah check version guna file hash - add check version by file hash function

class WPVersion {
	var $url;
	var $pattern = '([^\r\n"\']+\.[^\r\n"\']+)';
	protected $homepage_sc;
	
	function __construct($host, $src) {
		$this->url = $host;
		$this->homepage_sc = $src;
	}
	
	function get_version() {
		if($version = $this->meta_generator()) {
			return array('version' => $version, 'method' => 'Meta Generator');
		} elseif($version = $this->rss_feed()) {
			return array('version' => $version, 'method' => 'RSS Feed');
		} elseif($version = $this->rdf_generator()) {
			return array('version' => $version, 'method' => 'RDF Generator');
		} elseif($version = $this->atom_generator()) {
			return array('version' => $version, 'method' => 'Atom Generator');
		} elseif($version = $this->external_resources()) {
			return array('version' => $version, 'method' => 'External Resources');
		} elseif($version = $this->readme()) {
			return array('version' => $version, 'method' => 'Readme File');
		} elseif($version = $this->links_opml()) {
			return array('version' => $version, 'method' => 'Links Opml');
		} elseif($version = $this->file_hash()) {
			return array('version' => $version, 'method' => 'File Hash');
		} else {
			return false;
		}
	}

	function external_resources() {
		$arr = [];
		preg_match_all("#<link.+?rel='stylesheet'.+?href='.+ver=(".$this->pattern.")'.+?/>#i", $this->homepage_sc, $match1);
		preg_match_all("#<script.+src='.+ver=(".$this->pattern.")'>#i", $this->homepage_sc, $match2);
		foreach(array_merge($match1[1], $match2[1]) as $ver) {
			if( isset($arr[$ver]) ) {
				$arr[$ver]++;
			} else {
				$arr[$ver] = 1;
			}
		}
		array_multisort(array_values($arr), SORT_DESC, array_keys($arr), SORT_DESC, $arr);
		return count($arr) ? key($arr) : false;
	}

	function file_hash() {
        // set user agent for md5_file, avoid to get ban
        if ( function_exists('_user_agents') ) {
            ini_set('user_agent', _user_agents() );
        }
		$data = json_decode(file_get_contents(ROOT_PATH . '/base/data/wp-version.json'), true);
		foreach ($data as $file => $hash) {
			if ( ( $md5 = @md5_file($this->url . '/' . $file) ) != false ) {
			    if(array_key_exists($md5, $hash)) {
				    return $hash[$md5];
			    } 
            } 
		}
		return false;
	}
	
	function meta_generator() {
		$data = $this->homepage_sc;
		preg_match('/name="generator" content="wordpress '.$this->pattern.'"/i', $data, $match);
		return isset($match[1]) ? $match[1] : false;
	}
	
	function rss_feed() {
		$data = HTTPRequest($this->url . '/feed/');
		preg_match('#<generator>http://wordpress.org/\?v='.$this->pattern.'</generator>#i', $data, $match);
		return isset($match[1]) ? $match[1] : false;
	}
	
	function rdf_generator() {
		$data = HTTPRequest($this->url . '/feed/rdf/');
		preg_match('#<admin:generatorAgent rdf:resource="http://wordpress.org/\?v='.$this->pattern.'" />#i', $data, $match);
		return isset($match[1]) ? $match[1] : false;
	}
	
	function atom_generator() {
		$data = HTTPRequest($this->url . '/feed/atom/');
		preg_match('#<generator uri="http://wordpress.org/" version="'.$this->pattern.'">WordPress</generator>#i', $data, $match);
		return isset($match[1]) ? $match[1] : false;
	}
	
	function readme() {
		$data = HTTPRequest($this->url . '/readme.html');
		preg_match('#<br />\sversion '.$this->pattern.'#i', $data, $match);
		return isset($match[1]) ? $match[1] : false;
	}
	
	function links_opml() {
		$data = HTTPRequest($this->url . '/wp-links-opml.php');
		preg_match('#generator="wordpress/'.$this->pattern.'"#i', $data, $match);
		return isset($match[1]) ? $match[1] : false;
	}
}
