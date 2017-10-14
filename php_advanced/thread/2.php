<?php

/* 报错的写法
$thread = new class extends Thread {
    public function run() {
        echo 'hello world'.PHP_EOL;
    }
}
print_r(get_class_methods($thread));

 */

class ttt extends Thread {
    public function run() {
        echo 'hello world'.PHP_EOL;
    }
}

$thread = new ttt();

//打印对象所有方法
var_dump(get_class_methods($thread));




/*
预定义常量

PTHREADS_INHERIT_ALL:1118481        // 线程的默认选项。线程开始的时候，pthreads 扩展会将环境复制到线程上下文中。 
PTHREADS_INHERIT_NONE:0             //新线程开始时，不继承任何内容。
PTHREADS_INHERIT_INI:1              // 新线程开始时，仅继承 INI 配置。
PTHREADS_INHERIT_CONSTANTS:16       //新线程开始时，继承用户定义的常量。 
PTHREADS_INHERIT_CLASSES:4096       //新线程开始时，继承用户定义的类。
PTHREADS_INHERIT_FUNCTIONS:256      //新线程开始时，继承用户定义的函数。
PTHREADS_INHERIT_INCLUDES:65536     //新线程开始时，继承包含文件。
PTHREADS_INHERIT_COMMENTS:1048576   //新线程开始时，继承所有的注释。
PTHREADS_ALLOW_HEADERS:268435456    //允许新线程向标准输出发送头信息（通常情况下是被禁止的）。

*/