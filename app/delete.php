<?php
require_once '../config/common.php';

use classes\Auth;
use classes\PpdbRepository;

$auth = new Auth();
$ppdb = new PpdbRepository();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('this url is for webhook.');
}
if (!$auth->isLogin()) {
    header('Location: /login');
    die();
}

$ppdb->delete($_POST['id']);
$_SESSION['message'] = 'Data deleted successfully';
header('Location: ../index.php');
