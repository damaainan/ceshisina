# [Redis构建分布式锁][0]


<font face=微软雅黑>

**阅读目录**

* [1、前言][1]
* [2、简单理解redis的单线程IO多路复用][2]
* [3、并发测试][3]
* [4、事务解决与原子性操作解决][4]
* [4.1、事务解决][5]
* [4.2、原子性操作incr解决][6]
* [5、构建分布式锁][7]


#### 1、前言

为什么要构建锁呢？因为构建合适的锁可以在高并发下能够保持数据的一致性，即客户端在执行连贯的命令时上锁的数据不会被别的客户端的更改而发生错误。同时还能够保证命令执行的成功率。

看到这里你不禁要问redis中不是有事务操作么？事务操作不能够实现上面的功能么？

的确，redis中的事务可以watch可以监控数据，从而能够保证连贯执行的时数据的一致性，但是我们必须清楚的认识到，在多个客户端同时处理相同的数据的时候，很容易导致事务的执行失败，甚至会导致数据的出错。

在关系型数据库中，用户首先向数据库服务器发送BEGIN，然后执行各个相互一致的写操作和读操作，最后用户可以选择发送COMMIT来确认之前的修改，或者发送ROLLBACK进行回滚。

在redis中，通过特殊的命令MULTI为开始，之后用户传入一连贯的命令，最后EXEC为结束（在这一过程中可以使用watch进行监控一些key）。进一步分析，redis事务中的命令会先推入队列，等到EXEC命令出现的时候才会将一条条命令执行。假若watch监控的key发生改变，这个事务将会失败。这也就说明Redis事务中不存在锁，其他客户端可以修改正在执行事务中的有关数据，这也就为什么在多个客户端同时处理相同的数据时事务往往会发生错误。


#### 2、简单理解redis的单线程IO多路复用

Redis采用单线程IO多路复用模型来实现高内存数据服务。何为单线程IO多路复用呢？从字面的意思可以知道redis采用的是单线程、使用的是多个IO。整个过程简单的来讲就是，哪个命令的数据流先到达就先执行。

请看下面的形象理解图：图中是一座窄桥，**只能允许一辆车通过**，左边是车辆进入的通道，哪一辆车先到达就先进入。即哪个IO流先到达就先处理哪个。

Linux下网络IO使用socket套接字来通讯，普通IO模型只能监听一个socket，而IO多路复用可同时监控多个socket。IO多路复用避免阻塞在IO上，单线程保存多个socket的状态后轮循处理。

![][9]


#### 3、并发测试

我们就模拟一个简单典型的并发测试，然后从这个测试中得出问题，再进一步研究。

并发测试思路：

1、在redis中设置一个字符串count，运用程序将其取出来加+1，再存储回去，一直循环十万次

2、在两个浏览器上同时执行这个代码

3、将count取出来，查看结果

测试步骤：

1、建立test.php文件
 
```php
<?php
$redis=new Redis();
$redis->connect('192.168.95.11','6379');
for ($i=0; $i < 100000; $i++) 
{ 
  $count=$redis->get('count');
  $count=$count+1;
  $redis->set('count',$count);  
}
echo "this OK";
?>
```

2、分别在两个浏览器中访问test.php文件

![][10]

结果由上图可知，总共执行两次，count原本应该是二十万才对的，但实际上count等于十三万多，远远小于二十万，这是为什么呢？

由前面的内容可知，redis是采用单线程IO多路复用模型的。因此我们使用两个浏览器即为两个会话（A、B），取出、加1、存入这三个命令并不是原子操作，并且在执行取出、存入这两个redis命令时是哪个客户端先到就先执行。

例如：1、此时count=120

2、A取出count=120，紧接着B的取出命令流到了，也将count=120取出

3、A取出后立即加1，并将count=121存回去

4、此时B也紧跟着，也将count=121存进去了

注意：

1、设置循环次数尽量大一点，太小的话，当在第一个浏览器执行完毕，第二个浏览器还没开始进行呢

2、必须要两个浏览器同时执行。假若在一个浏览器中同时执行两次test.php文件，不管是否同时执行，最终结果就是count=200000。因为在同一个浏览器中执行，都是属于同一个会话（所有命令都在同一个通道通过），所以redis会让先执行的十万次执行完，再接着执行其他的十万次。


#### 4、事务解决与原子性操作解决


#### 4.1、事务解决

更改后的test.php文件

```php
<?php
header("content-type: text/html;charset=utf8;");
$start=time();
$redis=new Redis();
$redis->connect('192.168.95.11','6379');

for ($i=0; $i < 100000; $i++) 
{ 
  $redis->multi();
  $count=$redis->get('count');
  $count=$count+1;
  $redis->set('count',$count);
  $redis->exec();
}
$end=time();
echo "this OK<br/>";
echo "执行时间为：".($end-$start);
?>
```
执行结果失败，表名使用事务不能够解决此问题。

![][11]

分析原因：

我们都知道当redis开启时，事务中的命令是不执行的，而是先将命令压入队列，然后当出现exec命令的时候，才会阻塞式的将所有的命令一个接一个的执行。

所以当使用PHP中的Redis类进行redis事务的时候，所有有关redis的命令都不会真正的执行，而仅仅是将命令发送到redis中进行存储起来。

因此下图中所圈到的$count实际上不是我们想要的数据，而是一个对象，因此test.php中11行出错。

![][12]

**查看对象count：**

![][13]

![][14]


#### 4.2、原子性操作incr解决

#更新test.php文件

```php
<?php
header("content-type: text/html;charset=utf8;");
$start=time();
$redis=new Redis();
$redis->connect('192.168.95.11','6379');
for ($i=0; $i < 100000; $i++) 
{ 
  $count=$redis->incr('count');
}
$end=time();
echo "this OK<br/>";
echo "执行时间为：".($end-$start);
?>
```

两个浏览器同时执行，耗时14、15秒，count=200000，可以解决此问题。

缺点：

仅仅只是解决这里的取出加1的问题，本质上还是没能解决问题的，在实际环境中，我们需要做的是一系列操作，不仅仅只是取出加1，**因此就很有必要构建一个万能锁了。**


#### 5、构建分布式锁 

我们构造锁的目的就是在高并发下消除选择竞争、保持数据一致性

构造锁的时候，我们需要注意几个问题：

1、预防处理持有锁在执行操作的时候进程奔溃，导致死锁，其他进程一直得不到此锁

2、持有锁进程因为操作时间长而导致锁自动释放，但本身进程并不知道，最后错误的释放其他进程的锁

3、一个进程锁过期后，其他多个进程同时尝试获取锁，并且都成功获得锁

我们将不对test.php文件修改了，而是直接建立一个相对比较规范的面向对象Lock.class.php类文件 

#建立Lock.class,php文件

```php
<?php
#分布式锁
class Lock
{
    private $redis='';  #存储redis对象
    /**
    * @desc 构造函数
    * 
    * @param $host string | redis主机
    * @param $port int    | 端口
    */
    public function __construct($host,$port=6379)
    {
        $this->redis=new Redis();
        $this->redis->connect($host,$port);
    } 

    /**
    * @desc 加锁方法
    *
    * @param $lockName string | 锁的名字
    * @param $timeout int | 锁的过期时间
    *
    * @return 成功返回identifier/失败返回false
    */
    public function getLock($lockName, $timeout=2)
    {
        $identifier=uniqid();       #获取唯一标识符
        $timeout=ceil($timeout);    #确保是整数
        $end=time()+$timeout;
        while(time()<$end)          #循环获取锁
        {
            if($this->redis->setnx($lockName, $identifier))    #查看$lockName是否被上锁
            {
                $this->redis->expire($lockName, $timeout);     #为$lockName设置过期时间，防止死锁
                return $identifier;                             #返回一维标识符
            }
            elseif ($this->redis->ttl($lockName)===-1) 
            {　　　　　　　　　　　　　　　　　　　　　　　　　　　　　 　
                $this->redis->expire($lockName, $timeout);     #检测是否有设置过期时间，没有则加上（假设，客户端A上一步没能设置时间就进程奔溃了，客户端B就可检测出来，并设置时间）
            }
            usleep(0.001);         #停止0.001ms
        }
        return false;
    }

    /**
    * @desc 释放锁
    *
    * @param $lockName string   | 锁名
    * @param $identifier string | 锁的唯一值
    *
    * @param bool
    */
    public function releaseLock($lockName,$identifier)
    {
        if($this->redis->get($lockName)==$identifier)   #判断是锁有没有被其他客户端修改
        { 
            $this->redis->multi();
            $this->redis->del($lockName);   #释放锁
            $this->redis->exec();
            return true;
        }
        else
        {
            return false;   #其他客户端修改了锁，不能删除别人的锁
        }
    }

    /**
    * @desc 测试
    * 
    * @param $lockName string | 锁名
    */
    public function test($lockName)
    {
        $start=time();
        for ($i=0; $i < 10000; $i++) 
        { 
            $identifier=$this->getLock($lockName);
            if($identifier)
            {
              $count=$this->redis->get('count');
              $count=$count+1;
              $this->redis->set('count',$count);
              $this->releaseLock($lockName,$identifier);
            } 
        }
        $end=time();
        echo "this OK<br/>";
        echo "执行时间为：".($end-$start);
    }

}

header("content-type: text/html;charset=utf8;");
$obj=new Lock('192.168.95.11');
$obj->test('lock_count');

?>
```

测试结果：

在两个不同的浏览器中执行，最终结果count=200000，但是耗时相对较多，需要近八十多秒左右。但是在高并发下，对同一个数据，二十万次上锁执行释放锁的操作还是可以接受的，甚至已经很不错了。

以上的简单例子仅仅只是为了模拟并发测试并检验而已，实际上我们可以使用Lock.class.php中的锁结合自己的项目加以修改就可以很好地使用这个锁了。例如商城中的疯狂抢购、游戏中虚拟商城玩家买卖东西等等。

（以上是自己的一些见解，若有不足或者错误的地方请各位指出）

作者：[那一叶随风][15]

</font>

声明：本博客文章为原创，只代表本人在工作学习中某一时间内总结的观点或结论。转载时请在文章页面明显位置给出原文链接

[0]: http://www.cnblogs.com/phpstudy2015-6/p/6575775.html
[1]: #_label0
[2]: #_label1
[3]: #_label2
[4]: #_label3
[5]: #_label4
[6]: #_label5
[7]: #_label6
[8]: #_labelTop
[9]: ../img/1451071394.jpg
[10]: ../img/173291368.jpg
[11]: ../img/134427038.jpg
[12]: ../img/852099911.jpg
[13]: ../img/881069215.jpg
[14]: ../img/814538894.jpg
[15]: http://www.cnblogs.com/phpstudy2015-6/