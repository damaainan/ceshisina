<?php 
//匿名函数的调用
function printFunc(){
    $func = function($str) {
        echo $str;
    }; //带结束符

    $func("aaaaa");
}
printFunc();
echo "\n";
//使用use 关键字可以调用父级函数的变量
function TestFunc() {
    $a = 1;
    $func = function() use ($a) {
        echo $a;
    };
    $func($a);
}

TestFunc();
echo "\n";
//修改外面的变量值
function Func() {
    $a = 0;
    $func = function() use (&$a) {
             echo $a;
       $a++;
    };
    $func($a); //输出0
    echo $a; //输出1
}
Func();