<?php
$i = 0;
while ($i<5) {
    $pid = pcntl_fork();
    $random = rand(10, 50);
    if ($pid == 0) {
        sleep($random);
        exit();
    }
    echo "child {$pid} sleep {$random}\n";
    $i++;
}

pcntl_signal(SIGINT,  function($signo) {
     echo "Ctrl + C\n";
});

while (1) {
    $pid = pcntl_wait($status);
    var_dump($pid);
    pcntl_signal_dispatch();
}