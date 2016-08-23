<?php
header("Content-type:text/html; Charset=utf-8");
/**
 * 这里模拟登录我的博客
 * 
 * @link http://www.phpddt.com
 */
// include("snoopy.class.php");
// $snoopy =new Snoopy;
 




set_time_limit(0);  
require_once("Snoopy.class.php");  
$snoopy=new Snoopy();  
//登陆论坛  
$submit_url ="https://passport.csdn.net/account/login?from=http://my.csdn.net/my/mycsdn";
$submit_vars["loginmode"]="normal";  
$submit_vars["styleid"]="1";  
$submit_vars["cookietime"]="315360000";  
$submit_vars["loginfield"]="username";  
$submit_vars['name']="yueyanying";
$submit_vars['password']="2012512311";
$submit_vars["questionid"]="0";  
$submit_vars["answer"]="";  
$submit_vars["loginsubmit"]="提 交";  
$snoopy->submit($submit_url,$submit_vars);  
if($snoopy->results)  
{  
    // //获取连接地址  
    // $snoopy->fetchlinks("http://www.phpchina.com/bbs");  
    // $url=array();  
    // $url=$snoopy->results; 
    var_dump($snoopy->results);
}