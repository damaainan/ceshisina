## PHP 程序应该减少 brk 调用，否则性能会受影响

来源：[https://mp.weixin.qq.com/s/0uHcrFsMuLDD9SuFnGUg5A](https://mp.weixin.qq.com/s/0uHcrFsMuLDD9SuFnGUg5A)

时间 2018-12-19 11:19:31

 
昨天工作上遇到一个非常有意思的问题，特此分享给大家，也给大家提个醒，在 PHP 程序中尽量减少系统调用。在我们系统中有一个 cron 脚本，完成的主要工作就是从 memcached 中获取数据，然后同步到数据库中。平时运行的好好的，但昨天却遇到了问题，唯一的变化就是本次任务从 memcached 中获取的数据非常多，总共有 100 万条记录。话不多少，先上伪代码：

```php
//共100万个memcached数据
$tnum = 1000000;
//共1万个key，每个100条memcached数据
$knum = ceil($tnum/100);
$mem->connect("localhost", "11211");

for ($i = 1; $i <= $knum; $i++) 
    $k[] = $mckey."_".$i;

# 一次性从 memcached 中获取到数据
$emailmc = $mem->get($k);

$email = array();
foreach ($emailmc as $v) {
    $s     = unserialize($v);
    $s     = explode(",", $s);
    # 合并数组
    $email = array_merge($email, $s);
}

# 一次性导入到 mecached 中
importdb($email);
```
 
### 彪悍的 memcached
 
由于脚本本次运行对业务非常重要，我一直在监视，发现运行了半个小时也没有结束，开始我思索是不是memcached一次性获取太多了，导致memcached查询遇到问题了？
 
使用 wireshark 和 strace 抓取了相关数据，发现获取 memcached 非常快，几秒钟就返回了，赞一下 memcached 性能。
 
### brk
 
接下去继续分析，strace 出现了满屏的 brk 系统调用，如下：

```php
$ strace -p 27429 -T
brk(0x6d4c000)                          = 0x6d4c000 <0.000007>
brk(0x6d8c000)                          = 0x6d8c000 <0.000007>
brk(0x6dcc000)                          = 0x6dcc000 <0.000007>
brk(0x6e0c000)                          = 0x6e0c000 <0.000007>
brk(0x6e4c000)                          = 0x6e4c000 <0.000006>
```
 
虽然每次的 brk 调用响应并不慢，但次数太多了，那么到底什么是 brk？

```php
brk, sbrk - change data segment size


```
 
也就是说 brk 在不断的改变某个指针对象的内容，按照上面的伪代码，$email 变量不断的在调整内存大小，而且 $email 变量的内存越来越大，执行速度也越来越慢，而且执行到一定时间，php出现了内存不够的错误，我做了相关调整：

```php
ini_set('memory_limit', '500M');
$email = array();
foreach ($emailmc as $v) {
    $s     = unserialize($v);
    $s     = explode(",", $s);
    $email = array_merge($email, $s);
    echo memory_get_usage();
}
```
 
memory_limit 是限制 php 程序能够使用的内存大小，通过 memory_get_usage 函数发现，内存使用越来越大，虽然最后代码也能够运行，但却要花费至少半个小时。
 
### call_user_func_array
 
对于 php 程序来说，应用代码是涉及不到 brk 调用的，但如果能够减少调用，程序执行时间肯定会提高很多，现在的目的就是减少 array_merge 操作，我先修改了部分代码，分批次从 memcached 中获取：

```php
//共100万个memcached数据
$tnum = 1000000;
//共1万个key，每个100条memcached数据
$knum = ceil($tnum/100);
$mem->connect("localhost", "11211");

$j = 1;
for ($i = 1; $i <= $knum; $i++) {
    $k[] = $mckey."_".$i;
    if (count($k)>100) {
        $emailmc = $mem->get($k);
        foreach ($emailmc as $v) {
            $s     = unserialize($v);
            $s     = explode(",", $s);
            $emailarr[$j] = $s;
            $j++;
        }
        $k = array();
    }
}

# 要运行 100 次
for ($i=1;$i<=$j;$i++) {
    $email = array_merge($email,$emailarr[$j]);
}
importdb($email);
```
 
我分批次从 memcached 中获取数据，然后保存到 $emailarr 数组变量中，如果再循环 array_merge，虽然速度快了一些，但仍然要100次，运行速度仍然非常慢。
 
我思索是不是在 php 内部能够将 $emailarr 数组一次性合并呢？虽然有思路，但不知道具体如何操作，咨询了 php 大牛，提出了 call_user_func_array 函数。
 
修改如下：

```php
$email = call_user_func_array('array_merge', $email);
importdb($email);
```
 
代码居然2秒就返回了，避免了由 php 应用代码进行大量的 array_merge 合并，由 php 内部一次性完成了 array_merge。
 
可能有些同学说，为啥你不能从 memcached 中获取一部分数据就导入到数据库中呢？主要原因是后面代码太复杂，怕出现新的问题，所以本次的改造思路就是一次性获取到 $email 变量对应的数据。
 
总结：php 应用代码不会和系统调用直接产生联系，可系统调用非常昂贵，应该减少调用，所以在开发的时候，应该想象下php代码的运行逻辑，从而提升性能。
 
今年我写了一本新书[《深入浅出HTTPS：从原理到实战》][1] ，本书 github 地址是 https://github.com/ywdblog/httpsbook，大家可以一起讨论本书；如果觉得写的还可以，欢迎在豆瓣做个评论（地址：https://book.douban.com/subject/30250772/，或点击“原文连接”）；也可以关注我的公众号（ID：yudadanwx，虞大胆的叽叽喳喳），我会分享更多的原创文章。
 


[1]: http://mp.weixin.qq.com/s?__biz=MzAwOTU4NzM5Ng==&mid=2455769944&idx=1&sn=8cc681833f10177a3979f9546867ddc2&chksm=8cc9ecf3bbbe65e55569cf0fd27b7495dc95f40bb98c7624739f5de0a9a749e6c11ee7587c72&scene=21#wechat_redirect