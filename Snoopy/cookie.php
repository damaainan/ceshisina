<?php

header("Content-type:text/html;Charset=utf8");
$ch =curl_init();
curl_setopt($ch,CURLOPT_URL,'http://www.weibo.com');

$header = array();
//curl_setopt($ch,CURLOPT_POST,true);
//curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_HEADER,true);
curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
curl_setopt($ch,CURLOPT_COOKIE,'SINAGLOBAL=8062468627467.752.1458205942141; wvr=6; Hm_lvt_cdc2220e7553b2a2cd949e1765e21edc=1466418850,1466472305; un=jiachunhui1988@sina.cn; SSOLoginState=1466731643; _s_tentry=login.sina.com.cn; Apache=9015888462308.795.1466741000200; ULV=1466741000221:87:28:11:9015888462308.795.1466741000200:1466679144294; SUB=_2A256aMSSDeTxGedJ6FIZ8S3NzDiIHXVZH7FarDV8PUJbmtBeLULfkW8URMKVLL3FyX_gZBDrvof-iE3obQ..; SUBP=0033WrSXqPxfM725Ws9jqgMF55529P9D9WhOaEMKSk.Lxxebbn5-7OSC5JpX5o2p5NHD95QpS0e71h20eKMXWs4Dqcjgi--Xi-i2i-z0i--Xi-i2i-z0Ucj7gBtt; SUHB=0eb8xkKg1Et6gg; ALF=1498267642; SWB=usrmdinst_26; UOR=news.ifeng.com,widget.weibo.com,spr_web_360_hao360_weibo_t001;');


$content = curl_exec($ch);

echo "<pre>";print_r(curl_error($ch));echo "</pre>";
echo "<pre>";print_r(curl_getinfo($ch));echo "</pre>";
echo "<pre>";print_r($header);echo "</pre>";
echo "</br>",$content;