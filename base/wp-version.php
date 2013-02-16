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

// To Do
// Tambah check version guna file hash - add check version by file hash function

class WPVersion {
	var $url;
	var $pattern = '([^\r\n"\']+\.[^\r\n"\']+)';
	
	function __construct($host) {
		$this->url = $host;
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
		} elseif($version = $this->readme()) {
			return array('version' => $version, 'method' => 'Readme File');
		} elseif($version = $this->links_opml()) {
			return array('version' => $version, 'method' => 'Links Opml');
		} else {
			return false;
		}
	}
	
	function meta_generator() {
		$data = HTTPRequest($this->url);
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

?>