<?php
$cookie_file=tempnam('temp','cookie');
$ch = curl_init();
$url1 = "http://www.kuwo.cn/artist/content?name=水瀬いのり";
curl_setopt($ch,CURLOPT_URL,$url1);
curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
curl_setopt($ch,CURLOPT_HEADER,0);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
curl_setopt($ch, CURLOPT_ENCODING ,'gzip'); //加入gzip解析
//设置连接结束后保存cookie信息的文件
curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_file);
$content=curl_exec($ch);

curl_close($ch);

// $ch3 = curl_init();
// $url3 = "http://www.cdut.edu.cn/xww/dwr/call/plaincall/portalAjax.getNewsXml.dwr";
// $curlPost = "callCount=1&page=/xww/type/1000020118.html&httpSessionId=12A9B726E6A2D4D3B09DE7952B2F282C&scriptSessionId=295315B4B4141B09DA888D3A3ADB8FAA658&c0-scriptName=portalAjax&c0-methodName=getNewsXml&c0-id=0&c0-param0=string:10000201&c0-param1=string:1000020118&c0-param2=string:news_&c0-param3=number:5969&c0-param4=number:1&c0-param5=null:null&c0-param6=null:null&batchId=0";
// curl_setopt($ch3,CURLOPT_URL,$url3);
// curl_setopt($ch3,CURLOPT_POST,1);
// curl_setopt($ch3,CURLOPT_POSTFIELDS,$curlPost);
//
// //设置连接结束后保存cookie信息的文件
// curl_setopt($ch3,CURLOPT_COOKIEFILE,$cookie_file);
// $content1=curl_exec($ch3);
// curl_close($ch3);
