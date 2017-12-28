<?php  
//test.php  
  
 require 'Coroutine.php';  
  
$i = 10000;  
  
$c = new Coroutine();  
$c->start(task1());  
$c->start(task2());  
  
function task1(){  
    global $i;  
    echo "wait start" . PHP_EOL;  
    while ($i-- > 0) {  
        yield;  
    }  
    echo "wait end" . PHP_EOL;  
};  
  
function task2(){  
    echo "Hello " . PHP_EOL;  
    yield;  
    echo "world!" . PHP_EOL;  
}  