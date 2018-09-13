## [LNMP 性能优化之 PHP 性能优化](https://blog.csdn.net/ivan820819/article/details/78154089)

## 目录

* [PHP 性能优化初探][0]
    * [使用 PHP 其语法不恰当][1]
    * [使用 PHP 做其不擅长的事][2]
    * [使用 PHP 连接的服务不稳定][3]
    * [使用 PHP 但尚未排查出来的问题][4]
* [PHP 性能问题简析][5]
    * [PHP 运行流程][6]
    * [PHP 开销和速度排序][7]
* [PHP 语言级性能优化][8]
    * [尽可能地使用内置函数来完成任务][9]
    * [尽可能地使用高性能的内置函数来完成任务][10]
    * [尽可能避免使用魔法方法来完成任务][11]
    * [尽可能避免使用错误抑制符来完成任务][12]
    * [尽可能避免使用正则表达式来完成任务][13]
    * [尽可能避免在遍历或循环内做各种运算][14]
    * [尽可能避免在计算密集型的业务中使用][15]
* [PHP 周边级性能优化][16]
    * [Server 服务器][17]
    * [Network 网络][18]
    * [Database 数据库][19]
    * [Cache 缓存][20]
    * [File 文件][21]
* [PHP 瓶颈级性能优化][22]
    * [Opcode 优化][23]
    * [Runtime 优化][24]
    * [PHP 扩展编写][25]
* [PHP 性能分析工具][26]
    * [ApacheBench 的功能讲解与实践使用][27]
    * [Vld Opcache 的功能讲解与实践使用][28]
    * [Xhprof of PHP 7 的功能讲解与实践使用][29]

## PHP 性能优化初探

#### 使用 PHP 其语法不恰当

PHP 做一门弱类型动态语言，在上手容易和开发快速同时，也会导致一些新手写出不规范的代码。比如在递归当中连接数据库读取数据；一次性从文件中读取大量的数据，处理完后却不主动释放内存；在遍历和循环中重复计算某个变量等等；数组的键没有加引号导致先查找常量集，都会导致 PHP 程序性能下降。

#### 使用 PHP 做其不擅长的事

PHP 作为一门 Web 后端脚本语言，好处是能够快速实现 Web Application 所需功能，而且容易部署。缺点就是相对于强类型静态语言如 Java/C/C++ 来说，PHP 的性能较差，在实现计算密集型的业务时没有任何优势。同时也由于 PHP 是同步阻塞的 IO 模型，在高并发请求的场景下容易遇到瓶颈，需要通过 PHP 相关扩展来解决相关技术难题。

#### 使用 PHP 连接的服务不稳定

PHP 作为一门胶水语言,势必会连接各种各样服务。常见的服务如：MySQL、Redis、MongoDB 等数据库，C/C++、GO、Java 等语言编写的后端服务。倘若 PHP 所连接服务不稳定，势必也会对 PHP 造成一定的性能影响。

#### 使用 PHP 但尚未排查出来的问题

在某些情况，某个 PHP 程序或某段 PHP 代码莫名其妙地出现相当耗时的情况，不知道是 PHP 本身出现了问题，还是所用的框架出现了问题，亦或是 PHP 周边甚至是硬件的问题。这个时候就需要通过工具进行排查。常用的工具有：PHP-Xhprof、PHP-XDebug。

## PHP 性能问题简析

PHP 的底层是由 C 语言组成的。每次运行 PHP 的程序，都是需要经过 C 语言遍写的 Zend 引擎来解析 PHP 文件，将其编译成为 Opcodes 后再去执行。就这样一来一回就消耗了不少时间和硬件性能。

#### PHP 运行流程

1. Scanning（Lexing），将 PHP 代码转换为语言片段（Tokens）。
1. Parsing，将 Tokens 转换成简单而有意义的表达式（Expression）。
1. Compilation，将表达式编译成 Opocdes。
1. Execution，顺次执行 Opcodes，每次一条，从而实现 PHP 脚本的功能。

(*.php) -> scanner -> (Tokens) -> Parser -> (Expression) -> Compilation -> (Opcodes) -> Execution -> (Output)#### PHP 开销和速度排序

在日常使用中，PHP 各项性能开销和运行速度如下：

* 性能开销（从大到小） 
    * 硬盘 I/O
    * 数据库 I/O
    * 内存 I/O
    * 网络 I/O
* 运行速度（从快到慢） 
    * 内存 I/O
    * 数据库 I/O
    * 硬盘 I/O
    * 网络 I/O

读写网络数据本质也是硬盘操作，而且网络都是有延迟的，这也带了隐性的时间浪费，事实上 Web Application 感觉运行速度低下的原因，一般也都是网络 I/O 和 硬盘 I/O 引起的问题。

## PHP 语言级性能优化

#### 尽可能地使用内置函数来完成任务

能使用 PHP 内置方法解决的问题，就不要自己手写代码，一是手写代码一般冗余较多，可读性不佳。二是手写代码需要解析编译为底层代码再执行，没有 PHP 内置函数的性能高。

for & range() 实现同一功能
```php
<?php

for ($i = 0; $i <1000; $i++) {
    $array1[$i] = $i+1000;
}

range(1000,1999);
```
以 foreach、in_array 和 array_merge 实现同一功能对比说明：
```php
<?php

$arrayMerged = [];
foreach ($array1 as $value) {
    $arrayMerged[] = $value;
}
foreach ($array2 as $value) {
    if(!in_array($value, $arrayMerged)){
        $arrayMerged[] = $value;
    }
}

array_merge($array1,$array2);
```
以 foreach 和 array_column() 实现同一功能对比说明：
```php
<?php

$usernames = [];
foreach ($array as $key => $value) {
    if(isset($value['username']) && !empty($value['username'])){
        $usernames[$value['id']] = $value;
    }
}

array_column($array, 'username','id');
```
以 foreach 和 array_filter() 实现同一功能对比说明：
```php
<?php

$arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
foreach ($arr as $key => $value) {
    if($key === 'b'){
        $result[$key] = $value;
    }
    if($value === 4){
        $result[$key] = $value;
    }
}

array_filter($arr, function($v, $k) {
    return $k == 'b' || $v == 4;
}, ARRAY_FILTER_USE_BOTH)
```
#### 尽可能地使用高性能的内置函数来完成任务

在 PHP 内置的函数之间，实现同一个功能时，也会存在着性能的差距。这是因为 PHP 内置函数的时间复杂度各不相同，了解各个 PHP 内置函数的时间复杂度，也有利于从代码层优化 PHP 项目。

以 isset() 和 array_key_exists() 对比说明：
```php
<?php

function current_unix_time(){
    list($usec,$sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$time = -current_unix_time();
$i = 0;
$array = range(1, 1000000);
while ($i < 1000000) {
    if(array_key_exists($i, $array)){

    }
    $i++;
}
$time += current_unix_time();
echo $time."\n";

$time2 = -current_unix_time();
$i = 0;
$array = range(1, 1000000);
while ($i < 1000000) {
    if(isset($array[$i])){

    }
    $i++;
}
$time2 += current_unix_time();
echo $time2;

// array_key_exists：0.080096006393433
// isset：0.041244029998779
```
#### 尽可能避免使用魔法方法来完成任务

#### TODO

#### 尽可能避免使用错误抑制符来完成任务

#### TODO

#### 尽可能避免使用正则表达式来完成任务

正则表达式在 PHP 里算是一把双刃剑；它的优势就是使用起来简单。它的劣势就是性能低下。因为正则表达式需要回溯，而回溯的性能开销比较大，需要去优化。但优化正则表达式是需要一定的技术水平的。在常见的业务场景中，不妨使用字符串处理函数或 filter_var() 函数来实现功能。

#### 尽可能避免在遍历或循环内做各种运算

使用 for、while 循环的时候，循环内的计算式将会被重复计算，这样就造成了一些可避免的不必要性能开销。
```php
<?php

// 修改前
for($i = 0; $i < strlen("hello world"); $i++){
    //do something
}

// 修改后
$str = "hello world";
$strlen = strlen($str);
for($i = 0; $i < $strlen; $i++){
    //do something
}
```
#### 尽可能避免在计算密集型的业务中使用

什么是计算密集型的业务？一般来说是大批量的日志分析、大批量的数据运算。PHP 的语言特性决定了 PHP 不适合做大数据量的运算。因为 PHP 是由 C 语言编写的，  
PHP 的代码最终还是要转换成 C 语言去执行，这就需要一部分的性能开销。再加上 PHP 自身环境和语言特性缘故，PHP 做运算相比于 C 语言来说，不仅性能开销大，运算速度也慢，所以 PHP 不是适合做计算密集型的业务。PHP 的语言特性决定了它比较适合衔接后端服务或渲染页面。

## PHP 周边级性能优化

#### Server 服务器

从服务器方面进行优化，可以选择将服务器不安装其他后端服务软件，仅仅安装 PHP 以及其必要扩展。使单机的性能全部向 PHP 倾斜。同时也对 PHP 的相关参数进行优化，将 PHP 单机服务器性能最大化。在大数据、高并发的场景下，可以尝试将 PHP 服务器集群化，通过负载均衡，将网络请求分配至不同的 PHP 单机服务器处理。

#### Network 网络

从网络方面进行优化时的需要注意的两点：

* 对接接口是否稳定
* 连接网络是否稳定

如何优化网络：

* 使用更好的网卡：使用千兆以上的网卡
* 选择更好的套餐：更多的流量，更高的带宽
* 设置超时时间： 
    * 连接超时：200 ms
    * 读取超时：800 ms
    * 写入超时：500 ms
    * 可以根据实际情况进行自由调整
* 将串行的网络连接并行化 
    * 使用 curl_multi_*() 系列函数
    * 使用 Swoole 扩展或 Wokerman
* 压缩网络请求数据 
    * 启用 Server 的 Gzip 相关功能，最好是网络传输的数据大于 100 KB 的时候再使用，否则压缩包比源数据还大就徒劳了。
    * 压缩优势是减少客户端获取数据速度；劣势是由于 Server 需要压缩数据，会产生额外的 CPU 开销；而 Client 需要解压数据，客户端也会产生额外的 CPU 开销。

#### Database 数据库

数据库可以进行优化的层面：

* 服务器硬件
* 数据库配置
* 数据库表结构
* SQL 语句和索引

以 MySQL 为例，在服务器硬件方面，可以选择多核的 CPU，现在数据库软件对多核 CPU 都有优化；在数据库配置方面，可以根据监控日志和性能压测数据进行调优；数据表结构方面可以根据需要存储的数据选择最优的数据类型,设计时可以根据数据库表的范式来设计减少冗余数据，也可以反范式设计提高查询性能；如果数据库表过大，可以采取垂直拆分和水平拆分。而 SQL 语句和索引方面，可以通过开启 mysqldumpslow 和 pt-query-digest 来查看慢查询。使用 EXPLAIN 分析 SQL 的执行计划；使用普通索引或复合索引增加查询数据；了解 MySQL 执行的细节写出最优解的 SQL 语句。

#### Cache 缓存

#### TODO

#### File 文件

#### TODO

## PHP 瓶颈级性能优化

#### Opcode 优化

* 启用 Zend Opcache，以 PHP 7 为例，在配置文件 php.ini 加入以下代码即可：
```ini
zend_extension=opcache.so
opcache.enable=1
opcache.enable_cli=1"
```

#### Runtime 优化

* [HHVM：http://wuduoyi.com/note/hhvm/][30]
* [PHP-JIT：http://www.laruence.com/2016/12/18/3137.html][31]

#### PHP 扩展编写

* [PHP 扩展开发入门：https://andot.gitbooks.io/bped/content/][32]
* [PHP 深入理解 PHP 内核：http://www.php-internals.com/book/][33]

## PHP 性能分析工具

#### ApacheBench 的参数讲解与基础使用

[ApacheBench 参数讲解与基础使用：https://github.com/luisedware/Archives/issues/2][34]

#### Vld Opcache 的参数讲解与基础使用

#### TODO

#### Xhprof of PHP 7 的参数讲解与基础使用

#### TODO

[0]: #1
[1]: #1.1
[2]: #1.2
[3]: #1.3
[4]: #1.4
[5]: #2
[6]: #2.1
[7]: #2.2
[8]: #3
[9]: #3.1
[10]: #3.2
[11]: #3.3
[12]: #3.4
[13]: #3.5
[14]: #3.6
[15]: #3.7
[16]: #4
[17]: #4.1
[18]: #4.2
[19]: #4.3
[20]: #4.4
[21]: #4.5
[22]: #5
[23]: #5.3
[24]: #5.4
[25]: #5.1
[26]: #6
[27]: #6.1
[28]: #6.2
[29]: #6.3
[30]: http://wuduoyi.com/note/hhvm/
[31]: http://www.laruence.com/2016/12/18/3137.html
[32]: https://andot.gitbooks.io/bped/content/
[33]: http://www.php-internals.com/book/
[34]: https://github.com/luisedware/Archives/issues/2