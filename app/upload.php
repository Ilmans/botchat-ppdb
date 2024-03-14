<?php

session_start();

use classes\Auth;

require_once '../config/common.php';
$auth = new Auth();

if (!$auth->isLogin()) {
    header('Location: login.php');
    exit;
}

// upload template

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['message'] = 'Method not allowed';
    header('Location: ../index.php');
    exit;
}

if (!isset($_FILES['file'])) {
    $_SESSION['message'] = 'File not found';
    header('Location: ../index.php');
    exit;
}

$file = $_FILES['file'];
// must be doc or docx
$allowedExtension = ['doc', 'docx'];
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
if (!in_array($extension, $allowedExtension)) {
    $_SESSION['message'] = 'File type not allowed';
    header('Location: ../index.php');
    exit;
}
//delete old template
try {
    $file1 = ROOT_PATH . '/upload/template.doc';
    $file2 = ROOT_PATH . '/upload/template.docx';
    if (file_exists($file1)) {
        unlink($file1);
    }
    if (file_exists($file2)) {
        unlink($file2);
    }
} catch (\Throwable $th) {
    //throw $th;
}
$filename = ROOT_PATH . '/upload/' . 'template.' . $extension;
if (!move_uploaded_file($file['tmp_name'], $filename)) {
    $_SESSION['message'] = 'Error upload file';
    header('Location: ../index.php');
    exit;
}

// send to index
$_SESSION['message'] = 'File uploaded successfully';
header('Location: ../index.php');
exit;
