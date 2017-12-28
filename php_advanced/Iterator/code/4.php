<?php
// yield 更重要的特性 能够接收一个值
function printer()
{
    while (true) {
        printf("receive: %s\n", yield);
    }
}
 
$printer = printer();
 
$printer->send('hello');
$printer->send('world');