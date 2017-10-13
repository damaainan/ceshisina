<?php 

set_error_handler("error_handler");
function error_handler($errno,$errstr,$errfile,$errline){
    $str=<<<EOF
         "errno":$errno
         "errstr":$errstr
         "errfile":$errfile
         "errline":$errline
EOF;
//获取到错误可以自己处理，比如记Log、报警等等
    echo $str;
}
echo $test;//$test未定义，会报一个notice级别的错误