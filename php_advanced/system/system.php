<?php 
/**
 * system() 
原型：string system (string command [, int return_var]) 
system()函数很其它语言中的差不多，它执行给定的命令，输出和返回结果。第二个参数是可选的，用来得到命令执行后的状态码。
 */

header("Content-type:text/html; Charset=utf-8");
system("ls -l"); 