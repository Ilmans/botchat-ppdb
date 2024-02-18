<?php

use classes\DocumentGenerator;
use classes\PpdbRepository;

require_once '../config/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST')  die('method not allowed');

$ppdb = new PpdbRepository();
$docGenerator  = new DocumentGenerator();
if (isset($_POST['template'])) {
    $filePath = file_exists(ROOT_PATH . '/upload/template.docx') ? ROOT_PATH . '/upload/template.docx' : ROOT_PATH . '/upload/template.doc';
} else {
    if (!isset($_POST['regno'])) die('id not found');
    $ppdbdetail = $ppdb->checkAndGetByRegistrationNumber($_POST['regno']);
    if (!$ppdbdetail)  die('data not found');
    $docGenerator->generateDocument($ppdbdetail);
    $filePath = ROOT_PATH . '/upload/documents/' . $ppdbdetail['registration_no'] . '.docx';
}

if (file_exists($filePath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}
