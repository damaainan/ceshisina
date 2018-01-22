<?php 

$client_ip = getip();  
$referer = getreferer();  
  
$allow_ip = '192.168.1.100';  
$allow_referer = 'http://www.amztool.cn';  
$useragent=$_SERVER['HTTP_USER_AGENT'];  
plog( 'client_ip='.$client_ip." || useragent=".$useragent.' ');  
echo '<br>client_ip=';  
echo $client_ip;  
echo " || useragent=";  
echo $useragent;  
  
echo '<hr><br>';  
// 获取访问者ip  
function getip(){  
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){  
        $cip = $_SERVER['HTTP_CLIENT_IP'];  
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){  
        $cip = $_SERVER['HTTP_X_FORWARDED_FOR'];  
    }elseif(!empty($_SERVER['REMOTE_ADDR'])){  
        $cip = $_SERVER['REMOTE_ADDR'];  
    }else{  
        $cip = '';  
    }  
    return $cip;  
}  
  
// 获取访问者来源  
function getreferer(){  
    if(isset($_SERVER['HTTP_REFERER'])){  
        return $_SERVER['HTTP_REFERER'];  
    }  
    return '';  
}  
  
function plog($message){  
    $file=__DIR__."/text.txt";  
  
     if($f  = file_put_contents($file, $message."\r\n",FILE_APPEND)){// 这个函数支持版本(PHP 5) 打印到文件  
  
        }  
}  