<?php
define('ROOT_PATH', dirname(realpath(__FILE__)) );
define('DS', DIRECTORY_SEPARATOR);
define('BIN_FOLDER', 'bin');

$file_name = 'app.phar';

$phar = new Phar( ROOT_PATH . DS . BIN_FOLDER . DS . $file_name, 0, 'app.phar' );
$phar->buildFromDirectory( ROOT_PATH . DS);
$phar->addFile( ROOT_PATH . DS .  'app.php');
$phar->setDefaultStub('app.php');
