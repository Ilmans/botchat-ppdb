<?php
require '../config/common.php';

use classes\Auth;
use classes\PpdbRepository;
use classes\Exporter;

$auth = new Auth();
if (!$auth->isLogin()) {
    header('Location: app/login.php');
    exit;
}

$ppdbRepository = new PpdbRepository();
$exporter = new Exporter($ppdbRepository);

$filename = $exporter->exportAllData();

// Download the file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
readfile($filename);

// Delete the file after download
unlink($filename);
