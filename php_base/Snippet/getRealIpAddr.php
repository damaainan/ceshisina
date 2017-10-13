<?php 

header("Content-type:text/html; Charset=utf-8");

function getRealIpAddr()  
{  
    if (!emptyempty($_SERVER['HTTP_CLIENT_IP']))  
    {  
        $ip=$_SERVER['HTTP_CLIENT_IP'];  
    }  
    elseif (!emptyempty($_SERVER['HTTP_X_FORWARDED_FOR']))  
    //to check ip is pass from proxy  
    {  
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];  
    }  
    else 
    {  
        $ip=$_SERVER['REMOTE_ADDR'];  
    }  
    return $ip;  
}