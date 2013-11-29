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

class WPUser {

    var $url;
    var $users = array();

    function __construct($url) {
        $this->url = $url;
    }

    function enumerate() {
        for($i = 1;; $i++) {
            $resp = HTTPRequest($this->url . '/?author=' . $i, null, null, 0);
            if(stripos($resp, '301 moved permanently') !== false) {
                preg_match('/\/author\/([^\/\b]+)\/?/i', $resp, $match);
                if(isset($match[1])) $users[] = $match[1];
                else break;
            } elseif(stripos($resp, '200 ok') !== false) {
                preg_match('/<body class="archive author author\-(.*?) author/si">', $resp, $match);
                if(isset($match[1])) $users[] = $match[1];
                else break;
            } else break;
        }
        if(!empty($users)) return $users;
        else return false;
    }

}