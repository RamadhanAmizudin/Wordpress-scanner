<?php
function download() {
    $latest_version = 'https://raw.github.com/RamadhanAmizudin/Wordpress-scanner/master/app.php';
    $src = HTTPRequest($latest_version);
    preg_match("#define\('Version', '(.*?)'\);#i", $src, $o);
    $url = "https://github.com/RamadhanAmizudin/Wordpress-scanner/archive/".$o[1].".zip";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1);
    $fp = fopen(basename($url), 'w+');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    $data = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    $file = $o[1].'.zip';
    $zip = new ZipArchive;
    $path = pathinfo(realpath($file), PATHINFO_DIRNAME);
    if($zip->open($file) === TRUE) {
      	$zip->extractTo($path);
      	$zip->close();
    }
}

