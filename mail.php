<?php

include('userClass.php');

if (!function_exists('getDB')) {
  include('DB.php');
}

$userClass = new userClass();

$debug = 2;

$toEmail = 'johngerome@gmail.com';
$toName = 'John Gerome';
$emailSubject = 'Test Email';
$emailBody = file_get_contents('forwardedEmail.html');
$emailBody = str_replace("[[link]]", 'http://google.com', $emailBody);

// Send Email
$userClass->sendEmail($toEmail, $toName, $emailSubject, $emailBody, $debug);
