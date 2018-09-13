<?php
/*** a simple array ***/
$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

try {
    /*** create a new object ***/
    $object = new CachingIterator(new ArrayIterator($array));
    foreach ($object as $value) {
        echo $value;
        if ($object->hasNext()) {
            echo ',';
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
