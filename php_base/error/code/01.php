<?php 
header("Content-type:text/html; Charset=utf-8");

//设置异常捕获函数
set_exception_handler("my_exception");
function my_exception($exception){
    echo 'Exception Catched:'.$exception->getMessage();
}
//抛出异常
throw new Exception("I am Exception");