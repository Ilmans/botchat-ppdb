<?php


require_once 'controllers/ProcessMessage.php';
require_once 'config/common.php';

use classes\ResponWebhookFormatter;
use controllers\ProcessMessage;

// this is simple php webhook for mpwa, not recommended using thi procedural pattern if you have a lot of keywrds!
header('content-type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);
if (!$data)  die('this url is for webhook.');
//file_put_contents('whatsapp.txt', '[' . date('Y-m-d H:i:s') . "]\n" . json_encode($data) . "\n\n", FILE_APPEND);
$message = strtolower($data['message']); // this is incoming message from whatsapp
$from = strtolower($data['from']); // this is the sender's whatsapp number
$bufferimage = isset($data['bufferImage']) ? $data['bufferImage'] : null; // this is the image buffer if the message is image

$respon = false; // 
$responFormatter = new ResponWebhookFormatter();
$processMessageClass = new ProcessMessage();
$respon =  $processMessageClass->process($message, $from, $bufferimage);
echo ($respon);
