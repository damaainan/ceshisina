<?php 
register_shutdown_function("error_handler");
function error_handler(){
    echo "Yeah,工作了!";
}