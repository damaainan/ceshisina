<?php
function echoTimes($msg, $max) {
    for ($i = 1; $i <= $max; ++$i) {
        echo "$msg iteration $i\n";
        yield;
    }
}
 
function task() {
    yield from echoTimes('foo', 10); // print foo ten times
    echo "---\n";
    yield from echoTimes('bar', 5); // print bar five times
}

foreach (task() as $item) {
    ;
}