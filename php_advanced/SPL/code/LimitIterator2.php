<?php

/*** a simple array ***/
$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

$it = new LimitIterator(new ArrayIterator($array));

try
{
    $it->seek(5);
    echo $it->current();
} catch (OutOfBoundsException $e) {
    echo $e->getMessage() . "<br />";
}
