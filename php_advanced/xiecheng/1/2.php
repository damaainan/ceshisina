<?php 
//Coroutine双向通信示例

function gen() {  
    $ret = (yield 'yield1');  
    echo "[gen]", $ret, "\n";  
    $ret = (yield 'yield2');  
    echo "[gen]", $ret, "\n";  
}  
  
$gen = gen();  
$ret = $gen->current();  
echo "[main]", $ret, "\n";  
$ret = $gen->send("send1");  
echo "[main]", $ret, "\n";  
$ret = $gen->send("send2");  
echo "[main]", $ret, "\n";  
  
/* 
 * [main]yield1 
 * [gen]send1 
 * [main]yield2 
 * [gen]send2 
 * [main] 
 */  