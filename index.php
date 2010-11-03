<?php
error_reporting(E_ALL);
ini_set('display_errors', '2');

$ini = parse_ini_file('system.ini');

if (!file_exists($ini['system_root'])) {
    die('<pre>Fatal Error: System path does not exist or system_root value is not defined at system.ini</pre>');
}

ini_set('include_path', $ini['system_root'] . PATH_SEPARATOR . '.');
require 'bootstrap.php';