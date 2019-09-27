<?php

ini_set('SMTP', "220.181.14.163");
// ini_set('SMTP', "SMTP.139.com");
// ini_set('SMTP', "163mx02.mxmail.netease.com");
ini_set('smtp_port', "25");
// ini_set('sendmail_from', "email@domain.com");

echo date("Y-m-d H:i:s");

// wamp 代发

// 只能 163 发 163

// $to      = "1881@139.com";
$to      = "junyuan802@163.com";
$subject = "贾俊园";
$message = '<html><body>';
$message .= "你算过命吗？我们不合适？你们合适？算出来有这些劫难了吗？";
$message .= "</body></html>";
$fromemail = "ceshi";
$fromname  = "jiajunyuan@sharklasers.com";
$fromname  = "master@example.com.cn";
// $fromname  = "12345678901@139.com";
// $fromname  = "me@gmail.com";
$lt        = '<';
$gt        = '>';
$sp        = ' ';
$from      = 'From:';
$headers   = 'From: ' . $from . "\r\n" .
'Reply-To: ' . $from . "\r\n" .
'X-Mailer: PHP/' . phpversion();
$headers = $from . $fromname . $sp . $lt . $fromemail . $gt;
$headers .= "MIME-Version: $fromname\r\n";
$headers .= "Content-Type: text/html; charset=utf-8\r\n";
mail($to, $subject, $message, $headers);
