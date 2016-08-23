<?php
header("Content-type:text/html; Charset=utf-8");
$u="jiachunhui1988@sina.cn";
$p="20125123112";
$password = $p;
$username = base64_encode($u);
$loginUrl = 'https://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.15)&_=1403138799543';
$loginData['entry'] = 'sso';
$loginData['gateway'] = '1';
$loginData['from'] = 'null';
$loginData['savestate'] = '30';
$loginData['useticket'] = '0';
$loginData['pagerefer'] = '';
$loginData['vsnf'] = '1';
$loginData['su'] = base64_encode($u);
$loginData['service'] = 'sso';
$loginData['sp'] = $password;
$loginData['sr'] = '1920*1080';
$loginData['encoding'] = 'UTF-8';
$loginData['cdult'] = '3';
$loginData['domain'] = 'sina.com.cn';
$loginData['prelt'] = '0';
$loginData['returntype'] = 'TEXT';
//var_dump($loginData);exit;

function loginPost($url,$data){
    global $cookie_file ;
    //echo $cookie_file ;exit;
    $tmp = '';
    if(is_array($data)){//拼接数据
        foreach($data as $key =>$value){
            $tmp .= $key."=".$value."&";
        }
        $post = trim($tmp,"&");
    }else{
        $post = $data;
    }
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2774.3 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
    curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_file);
    // curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_file);
    $return = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return $return;
}
$login = json_decode(loginPost($loginUrl,$loginData),true);
var_dump($login);
// exit;


$ch = curl_init();
$timeout = 3000; // set to zero for no timeout
curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2774.3 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch,CURLOPT_URL,"http://weibo.com");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_file);
$return = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
// $url="http://weibo.com";
// $resu=openUrl($url,$cookie_file);
var_dump($return);
var_dump($info);






















//
// function openUrl($url,$cookie_file)
// {
//     $ch = curl_init();
//     $timeout = 3000; // set to zero for no timeout
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
//     curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2774.3 Safari/537.36');
//
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//     curl_setopt($ch,CURLOPT_COOKIEFILE, $cookie_file);
//     curl_setopt($ch,CURLOPT_COOKIEJAR,$cookie_file);
//     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//     $handles = curl_exec($ch);
//     curl_close($ch);
//     return $handles;
// }
