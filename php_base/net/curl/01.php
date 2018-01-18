<?php
$ch = curl_init();  
curl_setopt($ch, CURLOPT_URL, "http://localhost/02.php");  //  向 02 发送请求
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.8.8'));  //构造IP  
curl_setopt($ch, CURLOPT_REFERER, "http://www.nowamagic.net/ ");   //构造来路  
curl_setopt($ch, CURLOPT_HEADER, 1);  
$out = curl_exec($ch);  
curl_close($ch); 
