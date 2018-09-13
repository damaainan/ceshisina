<?php
function echoTimes($msg, $max) {
    for ($i = 1; $i <= $max; ++$i) {
        echo "$msg iteration $i\n";
        yield;
    }
    return "$msg the end value : $i\n";
}

function task() {
    $end = yield from echoTimes('foo', 10);
    echo $end;
    $gen = echoTimes('bar', 5);
    yield from $gen;
    echo $gen->getReturn();
}

foreach (task() as $item) {
    ;
}