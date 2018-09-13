<?php
/*** the offset value ***/
$offset = 3;

/*** the limit of records to show ***/
$limit = 2;

$array = array('koala', 'kangaroo', 'wombat', 'wallaby', 'emu', 'kiwi', 'kookaburra', 'platypus');

$it = new LimitIterator(new ArrayIterator($array), $offset, $limit);

foreach ($it as $k => $v) {
    echo $it->getPosition() . '<br />';
}
