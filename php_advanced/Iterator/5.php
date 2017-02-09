<?php
// 同时进行 接收 和 返回
function printer()
{
    $i = 0;
    while (true) {
        printf("receive: %s\n", (yield ++$i));
    }
}
 
$printer = printer();
 
printf("%d\n", $printer->current());
$printer->send('hello');
printf("%d\n", $printer->current());
$printer->send('world');
printf("%d\n", $printer->current());