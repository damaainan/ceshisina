<?php 
include(dirname(__FILE__)."/test_phpdbg_inc.php"); 
class demo{     
    public function __construct(){
         echo __METHOD__.":".__LINE__."\n";     
    }
    public function func($param){
         $param++;
         echo "method func $param\n";
    }
    public function __destruct(){
         echo __METHOD__.":".__LINE__."\n";
    }
} 

function func(){     
    $param = "ali";
    $param = $param + "baba";
    echo "function func $param\n";
}

$demo = new demo();
$demo->func(1);
func();
phpdbg_inc_func();