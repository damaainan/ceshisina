<?php 
//基于generator的range()实现

function my_range($start, $end, $step = 1) {  
    for ($i = $start; $i <= $end; $i += $step) {  
        yield $i;  
    }  
}  
  
foreach (my_range(1, 10) as $num) {  
    echo $num, "\n";  
}  

/*
 * my_range()的实现推测
包含yield关键字的函数比较特殊，返回值是一个Generator对象，此时函数内语句尚未真正执行
Generator对象是Iterator接口实例，可以通过rewind()、current()、next()、valid()系列接口进行操纵
Generator可以视为一种“可中断”的函数，而yield构成了一系列的“中断点”
Generator类似于车间生产的流水线，每次需要用产品的时候才从那里取一个，然后这个流水线就停在那里等待下一次取操作
 */
// $range = my_range(1, 10);  
  
// var_dump($range);  
/* 
 * object(Generator)#1 (0) { 
 * } 
 */  
  
// var_dump($range instanceof Iterator);  
/* 
 * bool(true) 
 */  