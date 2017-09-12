# [Redis应用----消息传递][0]

<font face=微软雅黑>

**阅读目录**

* [1、摘要][1]
* [2、实现方法][2]
* [3、一对一消息传递][3]
* [4、多对多消息传递][4]


#### 1、摘要

消息传递这一应用广泛存在于各个网站中，这个功能也是一个网站必不可少的。常见的消息传递应用有，新浪微博中的@我呀、给你评论然后的提示呀、赞赞赞提示、私信呀、甚至是发微博分享的新鲜事；知乎中的私信呀、live发送过来的消息、知乎团队消息呀等等。


#### 2、实现方法

消息传递即两个或者多个客户端在相互发送和接收消息。

通常有两种方法实现：

第一种为消息推送。Redis内置有这种机制，publish往频道推送消息、subscribe订阅频道。这种方法有一个缺点就是必须保证接收者时刻在线（即是此时程序不能停下来，一直保持监控状态，假若断线后就会出现客户端丢失信息）

第二种为消息拉取。所谓消息拉取，就是客户端自主去获取存储在服务器中的数据。Redis内部没有实现消息拉取这种机制。因此我们需要自己手动编写代码去实现这个功能。

在这里我们，我们进一步将消息传递再细分为一对一的消息传递，多对多的消息传递（群组消息传递）。

【注：两个类的代码相对较多，因此将其折叠起来了】


#### 3、一对一消息传递

**例子1****：一对一消息发送与获取**

模块要求：

1、提示有多少个联系人发来新消息

2、信息包含发送人、时间、信息内容

3、能够获取之前的旧消息

4、并且消息能够保持7天，过期将会被动触发删除

Redis实现思路：

1、新消息与旧消息分别采用两个链表来存储

2、原始消息的结构采用数组的形式存放，并且含有发送人、时间戳、信息内容

3、在推入redis的链表前，需要将数据转换为json类型然后再进行存储

4、在取出新信息时应该使用rpoplpush来实现，将已读的新消息推入旧消息链表中

5、取出旧消息时，应该用旧消息的时间与现在的时间进行对比，若超时，则直接删除后面的全部数据（因为数据是按时间一个一个压进链表中的，所以对于时间是有序排列的）

**数据存储结构图：**

![][6]

PHP的实现代码：

```php
#SinglePullMessage.class.php
<?php
#单接接收者接收消息
class SinglePullMessage
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
    * @desc 发送消息（一个人）
    * 
    * @param $toUser   string    | 接收人
    * @param $messageArr array   | 发送的消息数组，包含sender、message、time 
    *
    * @return bool
    */
    public function sendSingle($toUser,$messageArr)
    {
        $json_message=json_encode($messageArr);    #编码成json数据
        return $this->redis->lpush($toUser,$json_message);      #将数据推入链表 
    }

    /**
    * @desc 用户获取新消息
    *
    * @param $user string | 用户名
    *
    * @return array 返回数组，包含多少个用户发来新消息，以及具体消息
    */
    public function getNewMessage($user)
    {
        #接收新信息数据，并且将数据推入旧信息数据链表中，并且在原链表中删除
        $messageArr=array();
        while($json_message=$this->redis->rpoplpush($user, 'preMessage_'.$user))
        {
            $temp=json_decode($json_message);   #将json数据变成对象
            $messageArr[$temp->sender][]=$temp;        #转换成数组信息
        }
        if($messageArr)
        {
            $arr['count']=count($messageArr);   #统计有多少个用户发来信息
            $arr['messageArr']=$messageArr;
            return $arr;
        }
        return false;
    }

    public function getPreMessage($user)
    {
        ##取出旧消息
        $messageArr=array();
        $json_pre=$this->redis->lrange('preMessage_'.$user, 0, -1);    #一次性将全部旧消息取出来
        foreach ($json_pre as $k => $v) 
        {
            $temp=json_decode($v);            #json反编码
            $timeout=$temp->time+60*60*24*7;  #数据过期时间  七天过期
            if($timeout<time())               #判断数据是否过期
            {
                if($k==0)                     #若是最迟插入的数据都过期了，则将所有数据删除
                {
                    $this->redis->del('preMessage_'.$user);
                    break;
                }
                $this->redis->ltrim('preMessage_'.$user, 0, $k);  #若检测出有过期的，则将比它之前插入的所有数据删除
                break;
            }
            $messageArr[$temp->sender][]=$temp;
        }
        return $messageArr;
    }

    /**
    * @desc 消息处理，没什么特别的作用。在这里这是用来处理数组信息，然后将其输出。 
    *
    * @param $arr array | 需要处理的信息数组
    *
    * @return 返回打印输出
    */
    public function dealArr($arr)
    {
        foreach ($arr as $k => $v) 
        {
            foreach ($v as $k1 => $v2) 
            {
                echo '发送人：'.$v2->sender.'    发送时间：'.date('Y-m-d h:i:s',$v2->time).'<br/>';
                echo '消息内容：'.$v2->message.'<br/>';
            }
            echo "<hr/>";
        }
    }


}
```

测试：

1、发送消息


 
```php
#建立test1.php
include './SinglePullMessage.class.php';
$object=new SinglePullMessage('192.168.95.11');
#发送消息
$sender='boss';     #发送者
$to='jane';         #接收者
$message='How are you';    #信息
$time=time();
$arr=array('sender'=>$sender,'message'=>$message,'time'=>$time);
echo $object->sendSingle($to,$arr);
```

2、获取新消息

```php
#建立test2.php
include './SinglePullMessage.class.php';
$object=new SinglePullMessage('192.168.95.11');
#获取新消息
$arr=$object->getNewMessage('jane');
if($arr)
{
    echo $arr['count']."个联系人发来新消息<br/><hr/>";
    $object->dealArr($arr['messageArr']);   
}
else
    echo "无新消息";
```
访问结果:

![][9]

3、获取旧消息

```php
#建立test3.php
include './SinglePullMessage.class.php';
$object=new SinglePullMessage('192.168.95.11');
#获取旧消息
$arr=$object->getPreMessage('jane');
if($arr)
{
    $object->dealArr($arr);
}
else
    echo "无旧数据";
```

#### 4、多对多消息传递

**例子2****：多对多消息发送与获取（即是群组）**

模块要求：

1、用户能够自行创建群组，并成为群主

2、群主可以拉人进来作为群组成员、并且可以踢人

3、用户可以直接退出群组

4、可以发送消息，每一位成员都可以拉取消息

5、群组的消息最大容纳量为5000条

6、成员可以拉取新消息，并提示有多少新消息

7、成员可以分页获取之前已读的旧消息

。。。。。功能就写这几个吧，有需要或者想练习的同学们可以增加其他功能，例如禁言、匿名消息发送、文件发送等等。

Redis实现思路：

1、群组的消息以及群组的成员组成采用有序集合进行存储。群组消息有序集合的member存储用户发送的json数据消息，score存储唯一值，将采用原子操作incr获取string中的自增长值进行存储；群组成员有序集合的member存储user，score存储非零数字（在这里这个score意义不大，我的例子代码中使用数字1为群主的score，其他的存储为2。当然这使用这个数据还可以扩展别的功能，例如群组中成员等级）可参考下面数据存储结构简图。

2、用户所加入的群组也是采用有序集合进行存储。其中，member存储群组ID，score存储用户已经获取该群组的最大消息分值（对应群组消息的score值）

3、用户创建群组的时候，通过原子操作incr从而获取一个唯一ID

4、用户在群中发送消息时，也是通过原子操作incr获取一个唯一自增长有序ID

5、在执行incr时，为防止并发导致竞争关系，因此需要进行加锁操作【redis详细锁的讲解可以参考：[Redis构建分布式锁][10][http://www.cnblogs.com/phpstudy2015-6/p/6575775.html][10]】

6、创建群组方法简要思路，任何一个用户都可以创建群组聊天，在创建的同时，可以选择时是否添加群组成员（参数通过数组的形式）。创建过程将会为这个群组建立一个群组成员有序集合（群组信息有序集合暂时不创建），接着将群主添加进去，再将群ID添加用户所参加的群组有序集合中。

**数据存储结构图：**

**![][11]**

![][12]

PHP的代码实现：

```php
#ManyPullMessage.class.php
<?php
class ManyPullMessage
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
    * @desc 用于创建群组的方法，在创建的同时还可以拉人进群组
    * 
    * @param $user   string   | 用户名，创建群组的主人
    * @param $addUser array   | 其他用户构成的数组
    *
    * @param $lockName string | 锁的名字，用于获取群组ID的时候用
    * @return int 返回群组ID
    */
    public function createGroupChat($user, $addUser=array(), $lockName='chatIdLock')
    {
        $identifier=$this->getLock($lockName);  #获取锁
        if($identifier)
        {
            $id=$this->redis->incr('groupChatID');       #获取群组ID
            $this->releaseLock($lockName,$identifier);   #释放锁
        }
        else
            return false;
        $messageCount=$this->redis->set('countMessage_'.$id, 0);  #初始化这个群组消息计数器
        #开启非事务型流水线，一次性将所有redis命令传给redis，减少与redis的连接
        $pipe=$this->redis->pipeline();   
        $this->redis->zadd('groupChat_'.$id, 1, $user);  #创建群组成员有序集合，并添加群主
        #将这个群组添加到user所参加的群组有序集合中
        $this->redis->zadd('hasGroupChat_'.$user, 0, $id);  
        foreach ($addUser as $v)    #创建群组的同时需要添加的用户成员
        {
            $this->redis->zadd('groupChat_'.$id, 2, $v);
            $this->redis->zadd('hasGroupChat_'.$v, 0, $id);
        }
        $pipe->exec();
        return $id;    #返回群组ID
    }

    /**
    * @desc 群主主动拉人进群
    *
    * @param $user       string | 群主名
    * @param $groupChatID   int | 群组ID
    * @param $addMembers array  | 需要拉进群的用户
    *
    * @return bool
    */
    public function addMembers($user, $groupChatID, $addMembers=array())
    {
        $groupMasterScore=$this->redis->zscore('groupChat_'.$groupChatID, $user);  #将groupChatName的群主取出来
        if($groupMasterScore==1)     #判断user是否是群主
        {
            $pipe=$this->redis->pipeline(); #开启非事务流水线
            foreach ($addMembers as $v) 
            {
                $this->redis->zadd('groupChat_'.$groupChatID, 2, $v);                 #添加进群
                $this->redis->zadd('hasGroupChat_'.$v, 0, $groupChatID); #添加群名到用户的有序集合中
            }
            $pipe->exec();
            return true;
        }
        return false;
    }

    /**
    * @desc 群主删除成员
    *
    * @param $user       string | 群主名
    * @param $groupChatID   int | 群组ID
    * @param $delMembers  array | 需要删除的成员名字
    *
    * @return bool
    */
    public function delMembers($user, $groupChatID, $delMembers=array())
    {
        $groupMasterScore=$this->redis->zscore('groupChat_'.$groupChatID, $user); 
        if($groupMasterScore==1)     #判断user是否是群主
        {
            $pipe=$this->redis->pipeline(); #开启非事务流水线
            foreach ($delMembers as $v) 
            {
                $this->redis->zrem('groupChat_'.$groupChatID, $v);                 
                $this->redis->zrem('hasGroupChat_'.$v, $groupChatID); 
            }
            $pipe->exec();
            return true;
        }
        return false;
    }

    /**
    * @desc 退出群组
    *
    * @param $user string     | 用户名
    * @param $groupChatID int | 群组名
    */
    public function quitGroupChat($user, $groupChatID)
    {
        $this->redis->zrem('groupChat_'.$groupChatID, $user);
        $this->redis->zrem('hasGroupChat_'.$user, $groupChatID);
        return true;
    }

    /**
    * @desc 发送消息
    *
    * @param $user string        | 用户名
    * @param $groupChatID int    | 群组ID
    * @param $messageArr array   | 包含发送消息的数组
    * @param $preLockName string | 群消息锁前缀，群消息锁全名为countLock_群ID
    *
    * @return bool
    */
    public function sendMessage($user, $groupChatID, $messageArr, $preLockName='countLock_')
    {
        $memberScore=$this->redis->zscore('groupChat_'.$groupChatID, $user); #成员score
        if($memberScore)
        {
            $identifier=$this->getLock($preLockName.$groupChatID);  #获取锁
            if($identifier)     #判断获取锁是否成功
            {
                $messageCount=$this->redis->incr('countMessage_'.$groupChatID);
                $this->releaseLock($preLockName.$groupChatID,$identifier);  #释放锁
            }
            else
                return false;
            $json_message=json_encode($messageArr);
            $this->redis->zadd('groupChatMessage_'.$groupChatID, $messageCount, $json_message);
            $count=$this->redis->zcard('groupChatMessage_'.$groupChatID);   #查看信息量大小
            if($count>5000) #判断数据量有没有达到5000条
            {   #数据量超5000，则需要清除旧数据
                $start=5000-$count;
                $this->redis->zremrangebyrank('groupChatMessage_'.$groupChatID, $start, $count);
            }
            return true;
        }
        return false;
    }

    /**
    * @desc 获取新信息
    *
    * @param $user string | 用户名
    *
    * @return 成功则放回json数据数组，无新信息返回false
    */
    public function getNewMessage($user)
    {
        $arrID=$this->redis->zrange('hasGroupChat_'.$user, 0, -1, 'withscores');    #获取用户拥有的群组ID
        $json_message=array();  #初始化
        foreach ($arrID as $k => $v)    #遍历循环所有群组，查看是否有新消息
        {
            $messageCount=$this->redis->get('countMessage_'.$k);    #群组最大信息分值数
            if($messageCount>$v)    #判断用户是否存在未读新消息
            {
                $json_message[$k]['message']=$this->redis->zrangebyscore('groupChatMessage_'.$k, $v+1, $messageCount);
                $json_message[$k]['count']=count($json_message[$k]['message']);  #统计新消息数量
                $this->redis->zadd('hasGroupChat_'.$user, $messageCount, $k);    #更新已获取消息
            }   
        }
        if($json_message)
            return $json_message;
        return false;
    }

    /**
    * @desc 分页获取群组信息
    *
    * @param $user    string  | 用户名 
    * @param $groupChatID int | 群组ID
    * @param $page        int | 第几页
    * @param $size        int | 每页多少条数据
    *
    * @return 成功返回json数据，失败返回false
    */
    public function getPartMessage($user, $groupChatID, $page=1, $size=10)
    {
        $start=$page*$size-$size;   #开始截取数据位置
        $stop=$page*$size-1;        #结束截取数据位置
        $json_message=$this->redis->zrevrange('groupChatMessage_'.$groupChatID, $start, $stop);
        if($json_message)
            return $json_message;
        return false;
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
            /*
            #这里的set操作可以等同于下面那个if操作，并且可以减少一次与redis通讯
            if($this->redis->set($lockName, $identifier array('nx', 'ex'=>$timeout)))
                return $identifier;
            */
            if($this->redis->setnx($lockName, $identifier))    #查看$lockName是否被上锁
            {
                $this->redis->expire($lockName, $timeout);     #为$lockName设置过期时间
                return $identifier;                             #返回一维标识符
            }
            elseif ($this->redis->ttl($lockName)===-1) 
            {
                $this->redis->expire($lockName, $timeout);     #检测是否有设置过期时间，没有则加上
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


}

?>
```

测试：

1、建立createGroupChat.php(测试创建群组功能)

执行代码并创建568、569群组（群主为jack）

 
```php
include './ManyPullMessage.class.php';
$object=new ManyPullMessage('192.168.95.11');
#创建群组
$user='jack';
$arr=array('jane1','jane2');
$a=$object->createGroupChat($user,$arr);
echo "<pre>";
print_r($a);
echo "</pre>";die;
```

![][13]

![][14]

2、建立addMembers.php(测试添加成员功能)

执行代码并添加新成员

 
```php
include './ManyPullMessage.class.php';
$object=new ManyPullMessage('192.168.95.11');
$b=$object->addMembers('jack','568',array('jane1','jane2','jane3','jane4'));
echo "<pre>";
print_r($b);
echo "</pre>";die;
```

![][15]

3、建立delete.php(测试群主删除成员功能)

 
```php
include './ManyPullMessage.class.php';
$object=new ManyPullMessage('192.168.95.11');
#群主删除成员
$c=$object->delMembers('jack', '568', array('jane1','jane4'));
echo "<pre>";
print_r($c);
echo "</pre>";die;
```

![][16]

4、建立sendMessage.php(测试发送消息功能)

多执行几遍，568、569都发几条

 
```php
include './ManyPullMessage.class.php';
$object=new ManyPullMessage('192.168.95.11');
#发送消息
$user='jane2';
$message='go go go';
$groupChatID=568;
$arr=array('sender'=>$user, 'message'=>$message, 'time'=>time());
$d=$object->sendMessage($user,$groupChatID,$arr);
echo "<pre>";
print_r($d);
echo "</pre>";die;
```

![][17]

![][18]

5、建立getNewMessage.php(测试用户获取新消息功能)
 
```php
include './ManyPullMessage.class.php';
$object=new ManyPullMessage('192.168.95.11');
#用户获取新消息
$e=$object->getNewMessage('jane2');
echo "<pre>";
print_r($e);
echo "</pre>";die;
```

![][19]

6、建立getPartMessage.php(测试用户获取某个群组部分消息)

（多发送几条消息，用于测试。568中共18条数据）

 
```php
include './ManyPullMessage.class.php';
$object=new ManyPullMessage('192.168.95.11');
#用户获取某个群组部分消息
$f=$object->getPartMessage('jane2', 568, 1, 10); 
echo "<pre>";
print_r($f);
echo "</pre>";die;
```

page=1，size=10

![][20]

page=2，size=10

![][21]

测试完毕，还需要别的功能可以自己进行修改添加测试。

这次整理这篇文章相对比较赶，心里已经想着快点整理完赶紧学习其他的技术啦，哈哈22333。各位大神请留步，恳请各位给点学习redis的指导意见，本人职业方向是PHP

（以上是自己的一些见解，若有不足或者错误的地方请各位指出）

作者：[那一叶随风][22]

</font>

声明：本博客文章为原创，只代表本人在工作学习中某一时间内总结的观点或结论。转载时请在文章页面明显位置给出原文链接

[0]: http://www.cnblogs.com/phpstudy2015-6/p/6629000.html
[1]: #_label0
[2]: #_label1
[3]: #_label2
[4]: #_label3
[5]: #_labelTop
[6]: ../img/533349970.jpg
[9]: ../img/1171409026.jpg
[10]: http://www.cnblogs.com/phpstudy2015-6/p/6575775.html
[11]: ../img/525382845.jpg
[12]: ../img/314135164.jpg
[13]: ../img/299182347.jpg
[14]: ../img/1869616678.jpg
[15]: ../img/2111905697.jpg
[16]: ../img/1022082976.jpg
[17]: ../img/2070076986.jpg
[18]: ../img/548885646.jpg
[19]: ../img/1260716236.jpg
[20]: ../img/774456705.jpg
[21]: ../img/687426376.jpg
[22]: http://www.cnblogs.com/phpstudy2015-6/