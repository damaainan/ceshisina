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
curl_setopt($ch, CURLOPT_URL, 'https://www.baidu.com/s?wd=%E8%B4%BE%E4%BF%8A%E5%9B%AD&rsv_spt=1&rsv_iqid=0x814c9e0d0002da73&issp=1&f=8&rsv_bp=1&rsv_idx=2&ie=utf-8&rqlang=cn&tn=baiduhome_pg&rsv_enter=1&rsv_t=beae2WcBI%2BH4hMOzeYzHsw49zef%2Fud1XE%2Bs4FcVV6bPpkHD6QRhfsXziT7kNsQwpLhO0&gpc=stf%3D1548950400%2C1552319998%7Cstftype%3D2&tfflag=1');

//伪造来源refer
curl_setopt($ch, CURLOPT_REFERER, $refer);
//伪造来源ip
curl_setopt($ch, CURLOPT_HTTPHEADER, $headerIp);

//提交post传参
// curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
//...各种curl属性参数设置
$out_put = curl_exec($ch);
curl_close($ch);
var_dump($out_put);