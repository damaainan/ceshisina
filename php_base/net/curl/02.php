<?php
function getClientIp() {  
    if (!empty($_SERVER["HTTP_CLIENT_IP"]))  
        $ip = $_SERVER["HTTP_CLIENT_IP"];  
    else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))  
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];  
    else if (!empty($_SERVER["REMOTE_ADDR"]))  
        $ip = $_SERVER["REMOTE_ADDR"];  
    else  
        $ip = "err";  
    return $ip;  
}
  
echo "<br />IP: " . getClientIp() . "";  
echo "<br />referer: " . $_SERVER["HTTP_REFERER"]; 