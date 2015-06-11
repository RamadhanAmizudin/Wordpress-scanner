<?php
function check_version() {
	$latest_version = 'https://raw.github.com/RamadhanAmizudin/Wordpress-scanner/master/app.php';
	$src = HTTPRequest($latest_version);
	preg_match("#define\('Version', '(.*?)'\);#i", $src, $o);
	msg("[+] Current Version: ".Version);
	if(isset($o[1])) {
		if( version_compare(Version, $o[1], '<') ) {
			Config::set('nl', true);
			msg("[!] Newest version is available");
		} else {
			Config::set('nl', true);
		  	msg("[!] No new version available");
		}
	}
}
?>
