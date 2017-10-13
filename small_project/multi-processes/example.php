<?php
require __DIR__.'/MultiProcesses.php';

use MultiProcesses\MultiProcesses;
$worker = new MultiProcesses([
        "workerNum" => 5,
        "reActive" => true,
    ]);
$worker->function['childProcessStart'] = function($workerId, $currentPid){
    for ($i = 1; $i <= 5; $i++) {
        echo "进程编号:{$workerId} ,进程ID:{$currentPid} ,数值:{$i} " .PHP_EOL;
        usleep(100000);
    }
};
$worker->start();