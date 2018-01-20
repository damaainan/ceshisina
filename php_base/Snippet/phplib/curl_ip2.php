<?php

$postData = array(
    "user" => "root",
    "pwd"  => "123456"
);

$headerIp = array(
    'CLIENT-IP:88.88.88.88',
    'X-FORWARDED-FOR:88.88.88.88',
);

$refer = 'http://www.nidagelail.com';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://longnian8.com/portal.php?x=11250');

//伪造来源refer
curl_setopt($ch, CURLOPT_REFERER, $refer);
//伪造来源ip
curl_setopt($ch, CURLOPT_HTTPHEADER, $headerIp);

//提交post传参
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
//...各种curl属性参数设置
$out_put = curl_exec($ch);
curl_close($ch);
var_dump($out_put);