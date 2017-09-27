<?php  

// 检查验证码是否正确
    session_start();
    require 'secoder.class.php';  //先把类包含进来，实际路径根据实际情况进行修改。  
    $vcode = new YL_Security_Secoder();      //实例化一个对象  
    //$vcode->entry();  
    $code = $_GET['code']; 
    echo $vcode->check($code);        
    //$_SESSION['code'] = $vc->getCode();//验证码保存到SESSION中