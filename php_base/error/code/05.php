<?php
register_shutdown_function("error_handler");
function error_handler(){
    echo "Yeah,it's worked!";
}
if(true){
   function test(){}
}
function test(){}