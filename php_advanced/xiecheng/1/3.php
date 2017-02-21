<?php

// Coroutine错误处理示例
function gen() {  
    $ret = (yield 'yield1');  
    echo "[gen]", $ret, "\n";  
    try {  
        $ret = (yield 'yield2');  
        echo "[gen]", $ret, "\n";  
    } catch (Exception $ex) {  
        echo "[gen][Exception]", $ex->getMessage(), "\n";  
    }     
    echo "[gen]finish\n";  
}  
  
$gen = gen();  
$ret = $gen->current();  
echo "[main]", $ret, "\n";  
$ret = $gen->send("send1");  
echo "[main]", $ret, "\n";  
$ret = $gen->throw(new Exception("Test"));  
echo "[main]", $ret, "\n";  
  
/* 
 * [main]yield1 
 * [gen]send1 
 * [main]yield2 
 * [gen][Exception]Test 
 * [gen]finish 
 * [main] 
 */   