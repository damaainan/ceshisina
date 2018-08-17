Laraveler、PHPer面试指南

> 其实就是最近找工作发现的一些你可能经常用，但是理论说不清楚的问题，这里做的收集整理

### PHP
- strrpos() strripos() stripos() strpos()  之间的区别
    * strrpos() 查找字符串在另一字符串中*最后一次*出现的位置，区分大小写
    * strripos() - 查找字符串在另一字符串中*最后一次*出现的位置（不区分大小写）
    * stripos() 查找字符串在另一字符串中*第一次*出现的位置（不区分大小写）
    * strpos() - 查找字符串在另一字符串中*第一次*出现的位置（区分大小写）
    
- array_map 和 array_walk 的区别?
    * array_walk 主要是要对数组内的每个值进行操作，操作结果影响原来的数组
    * array_map 主要是对数组中的值进行操作后返回数组，以得到一个新数组
    * walk 可以没有返回值 map要有，因为要填充数组  
    
- ucfirst lcfirst ucwords strtoupper strtolower 的区别
    * ucfirst() 函数把字符串中的首字符转换为大写。
    * lcfirst() - 把字符串中的首字符转换为小写
    * ucwords() - 把字符串中每个单词的首字符转换为大写
    * strtoupper() - 把字符串转换为大写
    * strtolower() - 把字符串转换为小写
    
- HTTP状态码有哪些？ 区别？
    * 10x 信息，服务器收到请求，需要请求者继续执行操作 （收到请求了，你丫继续操作）
        * 100 继续。客户端应继续其请求
        * 101 切换协议。服务器根据客户端的请求切换协议。只能切换到更高级的协议，例如，切换到HTTP的新版本协议
        
    * 20x 成功，操作被成功接收并处理 （请求成功了）
        * 200 请求成功。一般用于GET与POST请求 
        * 201 已创建。成功请求并创建了新的资源
        * 202 已接受。已经接受请求，但未处理完成
        * 203 非授权信息。请求成功。但返回的meta信息不在原始的服务器，而是一个副本
        * 204 无内容。服务器成功处理，但未返回内容。在未更新网页的情况下，可确保浏览器继续显示当前文档
        * 205 重置内容。服务器处理成功，用户终端（例如：浏览器）应重置文档视图。可通过此返回码清除浏览器的表单域
        * 206 部分内容。服务器成功处理了部分GET请求
        
    * 30x 重定向，需要进一步的操作以完成请求 （收到请求了，去别的地方处理吧）
        * 300 多种选择。请求的资源可包括多个位置，相应可返回一个资源特征与地址的列表用于用户终端（例如：浏览器）选择
        * 301 永久移动。请求的资源已被永久的移动到新URI，返回信息会包括新的URI，浏览器会自动定向到新URI。今后任何新的请求都应使用新的URI代替
        * 302 临时移动。与301类似。但资源只是临时被移动。客户端应继续使用原有URI
        * 303 查看其它地址。与301类似。使用GET和POST请求查看
        * 304 未修改。所请求的资源未修改，服务器返回此状态码时，不会返回任何资源。客户端通常会缓存访问过的资源，通过提供一个头信息指出客户端希望只返回在指定日期之后修改的资源
        * 305 使用代理。所请求的资源必须通过代理访问
        * 306 已经被废弃的HTTP状态码
        * 307 临时重定向。与302类似。使用GET请求重定向
        
    * 40x 客户端错误 请求包含语法错误或无法完成请求 （你丫自己操作错了，会不会玩？）
        * 400 客户端请求的语法错误，服务器无法理解
        * 401 未认证
        * 402 保留，将来使用
        * 403 服务器理解请求客户端的请求，但是拒绝执行此请求
        * 404 服务器无法根据客户端的请求找到资源（网页）。通过此代码，网站设计人员可设置"您所请求的资源无法找到"的个性页面
        * 405 客户端请求中的方法被禁止
        * 406 服务器无法根据客户端请求的内容特性完成请求
        * 407 请求要求代理的身份认证，与401类似，但请求者应当使用代理进行授权    
        * 408 服务器等待客户端发送的请求时间过长，超时
        * 409 服务器完成客户端的PUT请求是可能返回此代码，服务器处理请求时发生了冲突
        * 410 客户端请求的资源已经不存在。410不同于404，如果资源以前有现在被永久删除了可使用410代码，网站设计人员可通过301代码指定资源的新位置
        * 411 服务器无法处理客户端发送的不带Content-Length的请求信息
        * 412 客户端请求信息的先决条件错误
        * 413 由于请求的实体过大，服务器无法处理，因此拒绝请求。为防止客户端的连续请求，服务器可能会关闭连接。如果只是服务器暂时无法处理，则会包含一个Retry-After的响应信息
        * 414 请求的URI过长（URI通常为网址），服务器无法处理
        * 415 服务器无法处理请求附带的媒体格式
        * 416 客户端请求的范围无效
        * 417 服务器无法满足Expect的请求头信息
        
    * 50x 服务器错误，服务器在处理请求的过程中发生了错误（开发和运维还没死快抬上来）
        * 500 服务器内部错误，无法完成请求
        * 501 服务器不支持请求的功能，无法完成请求
        * 502 充当网关或代理的服务器，从远端服务器接收到了一个无效的请求
        * 503 由于超载或系统维护，服务器暂时的无法处理客户端的请求。延时的长度可包含在服务器的Retry-After头信息中
        * 504 充当网关或代理的服务器，未及时从远端服务器获取请求
        * 505 服务器不支持请求的HTTP协议的版本，无法完成处理 
              
- PHP7和PHP5的区别，具体多了哪些新特性？
    * 空合并操作符（Null Coalesce Operator）
        `$name = $name ?? "NoName";  // 如果$name有值就取其值，否则设$name成"NoName"`
    * 飞船操作符（Spaceship Operator）
        形式：(expr) <=> (expr)
        左边运算对象小，则返回-1；左、右两边运算对象相等，则返回0；左边运算对象大，则返回1。
```php
$name = ["Simen", "Suzy", "Cook", "Stella"];
 usort($name, function ($left, $right) {
     return $left <=> $right;
 });
 print_r($name);
```
* 常量数组（Constant Array）  
      PHP 7 之前只允许类/接口中使用常量数组，现在 PHP 7 也支持非类/接口的普通常量数组了。
```php
define("USER", [
    "name"  => "Simen",
    "sex"   => "Male",
    "age"   => "38",
    "skill" => ["PHP", "MySQL", "C"]
]);
 // USER["skill"][2] = "C/C++";  // PHP Fatal error:  Cannot use temporary expression in write context in...
```
 统一了变量语法   
```php
$goo = [
   "bar" => [
       "baz" => 100,
       "cug" => 900
   ]
];

$foo = "goo";

$$foo["bar"]["baz"];  // 实际为：($$foo)['bar']['baz']; PHP 5 中为：${$foo['bar']['baz']};
                             // PHP 7 中一个笼统的判定规则是，由左向右结合。
```
* Throwable 接口  
        这是 PHP 7 引进的一个值得期待的新特性，将极大增强 PHP 错误处理能力。PHP 5 的 try ... catch ... finally 无法处理传统错误，如果需要，你通常会考虑用 set_error_handler() 来 Hack 一下。但是仍有很多错误类型是 set_error_handler() 捕捉不到的。PHP 7引入 Throwable 接口，错误及异常都实现了 Throwable，无法直接实现 Throwable，但可以扩展 \Exception 和 \Error 类。可以用 Throwable 捕捉异常跟错误。\Exception 是所有PHP及用户异常的基类；\Error 是所有内部PHP错误的基类。
```php
$name = "Tony";
try {
    $name = $name->method();
} catch (\Error $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
}

try {
    $name = $name->method();
} catch (\Throwable $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
}

try {
    intdiv(5, 0);
} catch (\DivisionByZeroError $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
}
```
* use 组合声明  
        use 组合声明可以减少 use 的输入冗余。
```php
use PHPGoodTaste\Utils\{
     Util,
     Form,
     Form\Validation,
     Form\Binding
 };
```
* 一次捕捉多种类型的异常 / 错误  
        PHP 7.1 新添加了捕获多种异常/错误类型的语法——通过竖杠“|”来实现。
```php
try {
      throw new LengthException("LengthException");
    //   throw new DivisionByZeroError("DivisionByZeroError");
    //   throw new Exception("Exception");
} catch (\DivisionByZeroError | \LengthException $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
} catch (\Exception $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
} finally {
    // ...
}
```
* 可见性修饰符的变化  
        PHP 7.1 之前的类常量是不允许添加可见性修饰符的，此时类常量可见性相当于 public。PHP 7.1 为类常量添加了可见性修饰符支持特性。总的来说，可见性修饰符使用范围如下所示：         
         * 函数/方法：public、private、protected、abstract、final   
         * 类：abstract、final   
         * 属性/变量：public、private、protected   
         * 类常量：public、private、protected   
```php
class YourClass 
{
    const THE_OLD_STYLE_CONST = "One";

    public const THE_PUBLIC_CONST = "Two";
    private const THE_PRIVATE_CONST = "Three";
    protected const THE_PROTECTED_CONST = "Four";
}
```
* iterable 伪类型  
        PHP 7.1 引入了 iterable 伪类型。iterable 类型适用于数组、生成器以及实现了 Traversable 的对象，它是 PHP 中保留类名。
```php
$fn = function (iterable $it) : iterable {
    $result = [];
    foreach ($it as $value) {
        $result[] = $value + 1000;
    }
    return $result;
};

$fn([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
```
* 可空类型（Nullable Type）  
        PHP 7.1 引入了可空类型。看看新兴的 Kotlin 编程语言的一个噱头特性就是可空类型。PHP 越来越像“强类型语言了”。对于同一类型的强制要求，可以设置其是否可空。
```php
<?
$fn = function (?int $in) {
    return $in ?? "NULL";
};

$fn(null);
$fn(5);
$fn();  // TypeError: Too few arguments to function {closure}(), 0 passed in ...
```
* Void 返回类型  
```php
function first(): void {
    // ...
}

function second(): void {
    // ...
    return;
}
```
    * 性能提升了两倍
    * 结合比较运算符 (<=>)
    * 标量类型声明
    * 返回类型声明
        * try...catch 增加多条件判断，更多 Error 错误可以进行异常处理
        * 匿名类，现在支持通过new class 来实例化一个匿名类，这可以用来替代一些“用后即焚”的完整类定义
        [参考地址](http://php.net/manual/zh/migration70.new-features.php)
  
- 为什么PHP7比PHP5性能提升了？
    * 变量存储字节减小，减少内存占用，提升变量操作速度
    * 改善数组结构，数组元素和 hash 映射表被分配在同一块内存里，降低了内存占用、提升了 cpu 缓存命中率
    * 改进了函数的调用机制，通过优化参数传递的环节，减少了一些指令，提高执行效率
    
- 你知道哪些PHP自带的数组排序方法？
    * sort() 函数用于对数组单元从低到高进行排序。
    * rsort() 函数用于对数组单元从高到低进行排序。
    * asort() 函数用于对数组单元从低到高进行排序并保持索引关系。
    * arsort() 函数用于对数组单元从高到低进行排序并保持索引关系。
    * ksort() 函数用于对数组单元按照键名从低到高进行排序。
    * krsort() 函数用于对数组单元按照键名从高到低进行排序。
        
### laravel 模块
- 服务提供者是什么？     
    * 服务提供者是所有 Laravel 应用程序引导启动的中心, Laravel 的核心服务器、注册服务容器绑定、事件监听、中间件、路由注册以及我们的应用程序都是由服务提供者引导启动的。
- Contract的原理？   
     * Contract（契约）是 laravel 定义框架提供的核心服务的接口。Contract 和 Facades 并没有本质意义上的区别，其作用就是使接口低耦合、更简单。
- IoC容器是什么？
    * IoC（Inversion of Control）译为 「控制反转」，也被叫做「依赖注入」(DI)。什么是「控制反转」？对象 A 功能依赖于对象 B，但是控制权由对象 A 来控制，控制权被颠倒，所以叫做「控制反转」，而「依赖注入」是实现 IoC 的方法，就是由 IoC 容器在运行期间，动态地将某种依赖关系注入到对象之中。
   
    其作用简单来讲就是利用依赖关系注入的方式，把复杂的应用程序分解为互相合作的对象，从而降低解决问题的复杂度，实现应用程序代码的低耦合、高扩展。
   
    Laravel 中的服务容器是用于管理类的依赖和执行依赖注入的工具。
    [参考地址](http://www.cnblogs.com/DebugLZQ/archive/2013/06/05/3107957.html) 
    
- 依赖注入的原理？   
     * @overtrue 一句话解释：依赖注入只是一种模式：把当前类依赖的第三方实例通过参数传入的形式引入，但是如果手写依赖注入会比较费劲，管理起来也比较麻烦，因为要关心那么多类的依赖，于是就有了一个容器来自动解决这个问题，利用反射API检查类型，然后递归解决依赖。
  
- Facade是什么？  
    * Facades（一种设计模式，通常翻译为外观模式）提供了一个"static"（静态）接口去访问注册到 IoC 容器中的类。提供了简单、易记的语法，而无需记住必须手动注入或配置的长长的类名。此外，由于对 PHP 动态方法的独特用法，也使测试起来非常容易。
- 了解过Composer？实现原理是什么？
    * Composer 是 PHP 的一个依赖管理工具。工作原理就是将已开发好的扩展包从 packagist.org composer 仓库下载到我们的应用程序中，并声明依赖关系和版本控制。
  
### 缓存
- Redis、Memecache这两者有什么区别？
    * Redis 支持更加丰富的数据存储类型，String、Hash、List、Set 和 Sorted Set。Memcached 仅支持简单的 key-value 结构。
    * Memcached key-value存储比 Redis 采用 hash 结构来做 key-value 存储的内存利用率更高。
    * Redis 提供了事务的功能，可以保证一系列命令的原子性
    * Redis 支持数据的持久化，可以将内存中的数据保持在磁盘中
    * Redis 只使用单核，而 Memcached 可以使用多核，所以平均每一个核上 Redis 在存储小数据时比 Memcached 性能更高。
- Redis如何实现持久化？      
    * RDB 持久化，将 redis 在内存中的的状态保存到硬盘中，相当于备份数据库状态。
    * AOF 持久化（Append-Only-File），AOF 持久化是通过保存 Redis 服务器锁执行的写状态来记录数据库的。相当于备份数据库接收到的命令，所有被写入 AOF 的命令都是以 redis 的协议格式来保存的。
    
### 设计模式
- 了解哪些设计模式？
    *   ##### 创建性模式
        > 单例模式、简单工厂模式、工厂方法模式、抽象工厂模式、对象池模式、 原型模式
        
        ##### 结构性模式
        > 适配器模式、桥接模式、组合模式、装饰器模式、依赖注入、门面模式、链式操作、代理模式
       
        ##### 注册器模式
        > 行为性模式、观察者模式、责任链模式、模板方法、策略模式、访问者模式、遍历模式、空对象模式、状态模式、命令模式
       
     [参考资料](http://larabase.com/collection/5/post/143)
     熟记SOLID原则
     
### 数据库
- 什么是索引，作用是什么？常见索引类型有那些？Mysql 建立索引的原则？
    * 索引是一种特殊的文件,它们包含着对数据表里所有记录的引用指针，相当于书本的目录。其作用就是加快数据的检索效率。常见索引类型有主键、唯一索引、复合索引、全文索引。

    * 索引创建的原则
        * 最左前缀原理
        * 选择区分度高的列作为索引
        * 尽量的扩展索引，不要新建索引
    * 高并发如何处理？
        * 使用缓存
        * 优化数据库，提升数据库使用效率
        * 负载均衡
- MySQL日志格式以及优缺点？
  * Statement: 每一条会修改数据的sql都会记录在binlog中
    * 优点：binlog文件较小日志是包含用户执行的原始SQL,方便统计和审计出现最早，兼容较好
    * 缺点：存在安全隐患，可能导致主从不一致对一些系统函数不能准确复制或是不能复制           
  * ROW: 不记录sql语句上下文相关信息，仅保存哪条记录被修改
    * 优点：相比statement更加安全的复制格式在某些情况下复制速度更快（SQL复杂，表有主键）系统的特殊函数也可以复制更少的锁更新和删除语句检查是否有主键，如果有则直接执行，如果没有，看是否有二级索引，如再没有，则全表扫描  
    * 缺点：binlog比较大（myql5.6支持binlog_row_image）单语句更新（删除）表的行数过多，会形成大量binlog无法从binlog看见用户执行SQL（5.6中增加binlog_row_query_log_events记录用户的query）
  * Mixed: 是以上两种level的混合使用，一般的语句修改使用statment格式保存binlog，如一些函数，statement无法完成主从复制的操作，则采用row格式保存binlog,MySQL会根据执行的每一条具体的sql语句来区分对待记录的日志形式，也就是在Statement和Row之间选择一种.新版本的MySQL中队row level模式也被做了优化，并不是所有的修改都会以row level来记录，像遇到表结构变更的时候就会以statement模式来记录。至于update或者delete等修改数据的语句，还是会记录所有行的变更。
    * 优点：混合使用row和statement格式，对于DDL记录statument,对于table里的行操作记录为row格式。 如果使用innodb表，事务级别使用了READ_COMMITTED or READ_UMCOMMITTED日志级别只能使用row格式。 但是使用ROW格式中DDL语句还是会记录成statement格式。
    * 缺点：mixed模式中，那么在以下几种情况下自动将binlog模式由SBR模式改成RBR模式。 当DML语句更新一个NDB表 当函数中包含UUID时 2个及以上auto_increment字段的表被更新时 行任何insert delayed语句时 用UDF时 视图中必须要求使用RBR时，例如创建视图使用了UUID()函数   
    
# 其他待解决问题
   - 分库分表怎么设计
   - 如何处理 MySQL 死锁？
   - 谈谈你对闭包的理解
   - PHP 内存回收机制
   - 如何解决 PHP 内存溢出问题
   - 数据库优化的方法
   - 简述 Laravel 的运行原理
   - Laravel 路由实现原理
   - cookie 和 session 区别，session 保存在服务器的哪里？服务端是如何获取客户端的cookie？
   - 服务器集群搭建、负载均衡、反向代理
   - 服务器常用命令  
   
### 感谢
 [overtrue](https://github.com/overtrue)
 [赵金超](https://github.com/todayqq)
 以及所有[Laravel-China](https://laravel-china.org/topics)的小伙伴

