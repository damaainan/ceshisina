<?php 

# post
function ajaxPost($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    if($res === false) {
        var_dump(curl_errno($ch));
        var_dump(curl_error($ch));
    } else {
        var_dump($res);
        $arr = json_decode($res, TRUE);
        var_dump($arr);
    }
    curl_close($ch);
}
# get
function ajaxGet($url, $data) {
    
    $url .= '?';
    foreach($data as $key=>$row) {
        $url .= $key . '=' . $row . '&';
    }
    $url = substr($url, 0, -1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $res = curl_exec($ch);
    if($res === false) {
        var_dump(curl_errno($ch));
        var_dump(curl_error($ch));
    } else {
        var_dump($res);
        $arr = json_decode($res, TRUE);
        var_dump($arr);
    }
    curl_close($ch);
}