<?php
pcntl_signal(SIGINT,  function($signo) {
     echo "Ctrl + C\n";
}, false);

while (true) {
    pcntl_signal_dispatch();
    echo "123\n";
    $limit = sleep(2);
    echo "limit sleep [{$limit}] s\n";
}