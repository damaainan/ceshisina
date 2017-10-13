<?php
register_shutdown_function( "fatal_handler" );
set_error_handler("error_handler");

define('E_FATAL',  E_ERROR | E_USER_ERROR |  E_CORE_ERROR | 
        E_COMPILE_ERROR | E_RECOVERABLE_ERROR| E_PARSE );

//获取fatal error
function fatal_handler() {
    $error = error_get_last();
    if($error && ($error["type"]===($error["type"] & E_FATAL))) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        error_handler($errno,$errstr,$errfile,$errline);
  }
}
//获取所有的error
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