<?php 
/**passthru() 
原型：void passthru (string command [, int return_var]) 
passthru ()只调用命令，不返回任何结果，但把命令的运行结果原样地直接输出到标准输出设备上。所以passthru()函数经常用来调用象pbmplus （Unix下的一个处理图片的工具，输出二进制的原始图片的流）这样的程序。同样它也可以得到命令执行的状态码。 
*/

header("Content-type:text/html; Charset=utf-8");
passthru("ls"); 