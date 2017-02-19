<?php 
/**
 * exec()
原型：string exec (string command [, string array [, int return_var]]) 
exec ()函数与system()类似，也执行给定的命令，但不输出结果，而是返回结果的最后一行。虽然它只返回命令结果的最后一行，但用第二个参数array 可以得到完整的结果，方法是把结果逐行追加到array的结尾处。所以如果array不是空的，在调用之前最好用unset()最它清掉。只有指定了第二 个参数时，才可以用第三个参数，用来取得命令执行的状态码。
 */


header("Content-type:text/html; Charset=utf-8");
exec("ls -l",$res);
var_dump($res); 