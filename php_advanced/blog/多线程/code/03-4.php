<?php
for( $i = 1; $i <= 3 ; $i++ ){
    $pid = pcntl_fork();
    if( $pid > 0 ){
       // do nothing ...
    } else if( 0 == $pid ){
        echo "儿子".PHP_EOL;
        exit;
    }
}