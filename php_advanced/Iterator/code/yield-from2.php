<?php
function echoMsg($msg) {
    while (true) {
        $i = yield;
        if($i === null){
            break;
        }
        if(!is_numeric($i)){
            throw new Exception("Hoo! must give me a number");
        }
        echo "$msg iteration $i\n";
    }
}
function task2() {
    yield from echoMsg('foo');
    echo "---\n";
    yield from echoMsg('bar');
}
$gen = task2();
foreach (range(1,10) as $num) {
    $gen->send($num);
}
$gen->send(null);
foreach (range(1,5) as $num) {
    $gen->send($num);
}
//$gen->send("hello world"); //try it ,gay