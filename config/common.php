<?php
session_start();
define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost:8080/botchat-ppdb');
require_once ROOT_PATH . '/vendor/autoload.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'error.log');
