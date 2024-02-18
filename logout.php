<?php

// logout

use classes\Auth;

require 'config/common.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('this url is for webhook.');
}
$auth = new Auth();


$auth->logout();

header('Location: app/login.php');
