<?php
$event = new Event( $eventBase, SIGTERM, Event::SIGNAL | Event::PERSIST, function(){
    echo "signal term.".PHP_EOL;
} );