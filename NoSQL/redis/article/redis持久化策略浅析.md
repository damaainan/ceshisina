## redis持久化策略浅析

来源：[https://segmentfault.com/a/1190000009537768](https://segmentfault.com/a/1190000009537768)

作为目前作为流行的cash，redis除了支持丰富的数据类型之外，还支持对内存中u数据的持久化，这样一来便可以防止因为一些崩溃情况（突然间断电、内存吃满）造成的整个内存数据的丢失，这对我们来说无疑是巨大的帮助。这里我们简单的了解一下redis持久化的策略，下面是自己的一些总结，如有错误，请及时指正。

## redis实现数据持久化的两种策略
### 1、rdb（redis database）-- 快照持久化
#### 1.1、什么是rdb？

rdb是redis的一种数据持久化策略，redis将某一时间点的数据全部打包生成一个.rdb的文件，保存在磁盘中，当我们重启redis服务的时候，将会读取该rdb文件恢复数据库中的数据。

#### 1.2、如何生成rdb快照

 **手动生成rdb快照：** 


* redis客户端发送bgsave命令来创建rdb快照

* redis客户端发送save命令来创建rdb快照

* redis客户端发送shutdown命令，redis服务端会在收到命令后先产生rdb快照，再关闭服务



 **自动生成rdb快照：** 

* redis基于redis配置文件的 save规则进行自动生成快照，具体的配置项请参考1.3


#### 1.3、关于rdb持久化相关的配置

a、save选项的设置，最重要的一个配置，它直接决定了是否生成快照。redis支持设置多条save规则，当其中的一条规则满足后就会自动的执行bgsave命令(换句话说下面的这些规则是 或 的关系)，下面是redis安装时的默认save规则

| 设置的规则 | 规则解释 |
|-|-|
| save    900    1 | 距离上一次执行rdb快照时间超过900秒，并且至少有1个键发生了改变，便会触发备份操作 |
| save    300    10 | 距离上一次执行rdb快照时间超过300秒，并且至少有10个键发生了改变，便会触发备份操作 |
| save    60      1000 | 距离上一次执行rdb快照时间超过60秒，并且至少有1000个键发生了改变，便会触发备份操作 |


b、rdbcompression选项的设置，该规则决定是否对生成的rdb文件进行压缩

| 规则的选项 | 规则解释 |
|-|-|
| rdbcompression   yes（默认是开启的） | 对生成的rdb快照文件进行压缩 |
| rdbcompression      no | 不对生成的rdb快照文件进行压缩 |


c、dbfilename选项的设置，该规则决定了生成的.rdb文件的名字，默认情况下的配置为dbfilename dump.rdb

d、 stop-writes-on-bgsave-error规则的设置，该规则决定了当bgsave备份命令执行失败的时候，redis是停止接受客户端发送过来的写命令

| 规则的选项 | 规则解释 |
|-|-|
| stop-writes-on-bgsave-error  yes（默认是开启的） | 当最近的一次rdb快照生成失败，redis将不再接受相关的写命令，以此来提醒用户备份的失败 |
| stop-writes-on-bgsave-error      no | 最近一次的rdb快照生成失败，仍然接受redis客户端发送过来的写命令，不过需要要靠谱的监控系统提醒我们rdb快照失败了，否则不会有人知道的 |


e、rdbchecksum 是否校验rdb文件，这个配置项在实际的作用没怎么弄清楚,有了解的同学可以解释下

| 规则的选项 | 规则解释 |
|-|-|
| rdbchecksum  yes（默认是开启的） | 从版本RDB版本5开始，一个CRC64的校验就被放在了文件末尾。这会让格式更加耐攻击，但是当存储或者加载rbd文件的时候会有一个10%左右的性能下降,所以，为了达到性能的最大化，你可以关掉这个配置项 |
| rdbchecksum  no | 没有校验的RDB文件会有一个0校验位，来告诉加载代码跳过校验检查 |


f、dbfilename选项的设置，该规则决定了生成的.rdb文件的名字，默认情况下的配置为dbfilename dump.rdb

g、dir rdb文件要保存的位置

### 2、aof（append only file ） -- 只追加文件持久化
#### 2.1、什么是aof（append only file）?

aof同样是redis的持久化策略，采用该策略的时候，redis会将被执行的写命令添加到aof文件的末尾，该文件被保留在磁盘中。当重启redis服务的时候会优先（相对于rdb文件而言）读取aof文件，完成对redis数据的恢复。
#### 2.2、关于aof持久化相关的配置

a、appendonly，该选项决定了是否开启aof持久化策略

| 配置名称 | 配置选项 | 解释说明 |
|-|-|-|
| appendonly | yes / no | 决定是否开启aof策略，默认情况下是no |


b、appendfsync, 该选项决定了写入aof文件的频率

| 配置名称 | 配置选项 | 解释说明 |
|-|-|-|
| appendfsync | always | 每一个redis写命令都会被写入到aof文件中，这样会严重降低redis的速度 |
| appendfsync | everysec | 每秒钟执行一次同步，将这一秒钟之内接受到的命令写入aof文件中 |
| appendfsync | no | 并不是不进行aof持久化，而是让操作系统决定什么时候将命令写入aof文件中，这样我们丢失的数据将不可控 |


c、no-appendfsync-on-rewrite, 当服务器出现短暂性的阻塞的时候，通常情况下，如果我们执行bgsave或者bgrewriteof命令时，可能会造成服务的短暂挂起，此时该选项决定是否还将命令同步到aof文件

| 配置名称 | 配置选项 | 解释说明 |
|-|-|-|
| no-appendfsync-on-rewrite | yes | 当服务器出现短暂性阻塞的时候，不将命令同步到aof文件中，暂时放入内存，知道阻塞结束后再同步 |
| no-appendfsync-on-rewrite | no | 当服务器出现阻塞时仍然尝试将命令追加到aof文件 |


d、auto-aof-rewrite-percentage和auto-aof-rewite-min-size。由于，我们不断将redis执行的写命令追加到aof文件中，会导致aof文件越来越大，redis就想出来一个办法，在保证redis存储数据正确的前提下，要尽可能的减少aof文件存储的命令，从而达到帮aof文件 " 瘦身 "的目的。redis的想到的办法就是执行bgrewriteaof命令，对aof文件进行重写，而这两个选项则决定了在什么情况下出发该命令。这里需要提到的一点是，这两个选项通常是一起使用，共同来决定是否执行bgrewriteaof命令。

| 配置名称 | 配置选项 | 解释说明 |
|-|-|-|
| auto-aof-rewrite-percentage | 100（任意数字都可以） | 如果为100的话，就表示当aof文件相对于上次的aof文件大小要增加一倍，也就是now_file_size >= last_file_size * (1 + auto-aof-rewrite-percentage / 100 )，此时便会试着出发bgrewrite，当然还要看另外一个条件 |
| auto-aof-rewrite-min-size | 64mb（一个指定大小） | 如果为64mb的话，就表示当aof文件 >= 64mb 的时候，尝试进行bgrewrite重写 ，当然还要看另外一个条件 |


e、appendfilename，选项决定了产生的aof文件的名称，举个例子 : appendfilename "appendonly.aof"

f、dir aof文件要保存的位置，该选项同时决定了aof和rdb两个文件的保存位置

## 3、准备工作

为了方便测试工作，基于yii框架，写了一个测试脚本，脚本的内容如下：

```php
<?php
namespace app\commands;
use yii\console\Controller;
class HelloController extends Controller
{
    public static $START_TIME = null;

    public $number = 0;

    public function init()
    {
        parent::init();
        static::$START_TIME = time();
    }

    /**
     * redis database test
     */
    public function actionRedis()
    {
        $redis = \Yii::$app->redis;
        while(true) {
            $left_time = time() - static::$START_TIME;
            if($left_time > 10 && $this->number > 15) {
                echo "\r\n","total use {$left_time} seconds, and {$this->number} keys had been set","\r\n";
                break;
            } else {
                $key_name = "test:${left_time}:" . mt_rand(0, 9999);
                $redis->set($key_name, $key_name);
                echo $key_name,"\r\n";
            }
            $this->number++;
            sleep(1);
        }
    }
}

```
## 4、举个例子
#### 4.1、rdb策略持久化的例子

 **4.1.1、更改redis配置如下** 
```
save 15 10
rdbcompression no
```
 **4.1.2、执行测试脚本** 


![][0]

 **4.1.3、查看redis内存中的数据** 


![][1]

 **4.1.4、查看dump.rdb文件** 
经过大约15秒左右的时间，我们发现在在指定的data目录下产生了一个dump.rdb文件


![][2]

 **4.1.5、强杀redis服务，模拟宕机情况** 


![][3]

 **4.1.6、重启redis服务，查看redis内存中的数据** 


![][4]

经过对比发现，丢失了一些key
#### 4.2、aof策略持久化的例子

 **更新redis的配置如下** 
```
appendonly yes
appendfilename "appendonly.aof"
appendfsync everysec
```

测试的流程大致相同，这里不再详细介绍，相同的测试脚本再次执行，发现key并没有任何丢失，这是由于redis每秒钟同步一次的缘故，在这种配置下，如果写入很频繁，也就是丢失1秒钟的数据
## 5、参考资料

《redis实战》
《redis入门指南》
[http://redisdoc.com/topic/per...][5]
[https://my.oschina.net/wfire/...][6]
[http://blog.nosqlfan.com/html...][7]

[5]: http://redisdoc.com/topic/persistence.html
[6]: https://my.oschina.net/wfire/blog/301147
[7]: http://blog.nosqlfan.com/html/4077.html
[0]: ./img/bVObkR.png
[1]: ./img/bVOblm.png
[2]: ./img/bVOblC.png
[3]: ./img/bVOblF.png
[4]: ./img/bVOblS.png