<?php
function check_version() {
	$latest_version = 'https://raw.github.com/RamadhanAmizudin/Wordpress-scanner/master/wordress-scanner.php';
	$src = HTTPRequest($latest_version);
	preg_match("#define\('Version', '(.*?)'\);#i", $src, $o);
	if(isset($o[1])) {
		if( version_compare(Version, $o[1], '<') ) {
			msg("[!] Newest version is available");
		}
	}
}