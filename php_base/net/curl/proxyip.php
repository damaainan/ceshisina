<?php
// **$header //请求头**

// **$cookie //存储cookie**

// **$arrip //代理IP的地址及端口**

// **$params //参数 你要提交的**

// **$method //请求方式（GET,POST）**

function dorequest($arrip = array(), $url, $header, $timeout = 20000, $method = '', $cookie) {

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式

    curl_setopt($ch, CURLOPT_PROXY, "$arrip[0]"); //代理服务器地址

    curl_setopt($ch, CURLOPT_PROXYPORT, $arrip[1]); //代理服务器端口

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch, CURLOPT_URL, $url); //设置链接

//curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0");

    if (!defined('CURLOPT_TIMEOUT_MS')) {

        $res = curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置1秒超时

    } else {

        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);

    }

    if ($cookie) {

        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie); //存储cookies

        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);}

    if (!defined('CURLOPT_CONNECTTIMEOUT_MS')) {

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);

    } else {

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout);}

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //设置是否返回信息

    $method = strtoupper($method);

    if ($method == 'POST') {

        curl_setopt($ch, CURLOPT_POST, 1); //设置为POST方式

        curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));

    }

    if ($header) {

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    } //设置跳转location 最多3次

    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

    $response = curl_exec($ch); //接收返回信息

}
