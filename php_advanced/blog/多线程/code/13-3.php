<?php
$timer = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, function () use (&$custom) {
    //echo microtime( true )." : 歼15，滑跃，起飞！".PHP_EOL;
    print_r($custom);
}, $custom = array(
    'name' => 'woshishui',
));
