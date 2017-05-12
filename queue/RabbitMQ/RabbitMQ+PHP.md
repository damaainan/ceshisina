# [RabbitMQ + PHP （一）入门与安装][0]


**RabbitMQ：**

1、是实现AMQP（高级消息队列协议）的消息中间件的一种。

2、主要是为了实现系统之间的双向解耦而实现的。当生产者大量产生数据时，消费者无法快速消费，那么需要一个中间层。保存这个数据。

一般提到 RabbitMQ 和消息，都会用到以下一些专有名词：

（1）生产(**Producing**)意思就是发送。发送消息的程序就是一个生产者(producer)。我们一般用 "P" 来表示。

（2）队列(**queue**)就是邮箱的名称。消息通过你的应用程序和 RabbitMQ 进行传输，它们能够只存储在一个队列（queue）中。 队列（queue）没有任何限制，你要存储多少消息都可以——基本上是一个无限的缓冲。多个生产者（producers）能够把消息发送给同一个队列，同样，多个消费者（consumers）也能够从同一个队列（queue）中获取数据。

（3）消费（**Consuming**）和获取消息是一样的意思。一个消费者（consumer）就是一个等待获取消息的程序。

PS：需要注意的是生产者、消费者、代理需不要待在同一个设备上；事实上大多数应用也确实不在会将他们放在一台机器上。

那么开始了解一下 RabbitMQ 在Windows下的安装于运用吧。

（一）RabbitMQ安装

（1）下载与安装erlang（安装RabbitMQ需要先安装erlang） 地址：[http://www.erlang.org/download.html][1]

（2）下载与安装RabbitMQ 下载地址：[http://www.rabbitmq.com/download.html][2]

![][3]

（二）测试安装结果

（1）操作起来很简单，只需要在DOS下面，进入安装目录（C：\RabbitMQ Server\rabbitmq_server-3.2.2\sbin）执行如下命令就可以成功安装。

（2）可以通过访问[http://localhost:15672][4]进行测试，默认的登陆账号为：guest，密码为：guest。

![][5]

（三）如果访问成功了，恭喜，整个RabbitMQ安装完成了。

（四）下篇会讲到 RabbitMQ + PHP 的AMQP拓展安装。






# [RabbitMQ + PHP （二）AMQP拓展安装][6]

上篇说到了 RabbitMQ 的安装。

这次要在讲案例之前，需要安装PHP的AMQP扩展。不然可能会报以下两个错误。

1.Fatal error: Class 'AMQPConnection' not found

![][7]

2. Fatal error: Uncaught exception 'AMQPConnectionException' with message 'Socket error: could not connect to host.'

![][8]

以上两个错误都是因为没有安装AMQP拓展 导致php在执行的时候报错了。 

解决办法：

1. 根据自身PHP的版本下载AMQP拓展 [https://pecl.php.net/package/amqp][9]

2. 将php_amqp.dll 放入**php/ext/**下 然后 php.ini中添加： **extension=php_amqp.dll**

3. 复制 **rabbitmq.1.dll**到 php目录 如我的放到 **G:/php/php5.6.25** 目录下

4. 修改 apache配置文件 httpd.conf添加入：

**Include "${INSTALL_DIR}/alias/*"**  
**LoadModule php5_module "${INSTALL_DIR}/bin/php/php5.6.25/rabbitmq.1.dll"**

5. 重启 apache 和 php 服务即可






# [RabbitMQ + PHP （三）案例演示][10]


今天用一个简单的案例来实现 RabbitMQ + PHP 这个消息队列的运行机制。

主要分为两个部分：

第一：发送者（publisher）

第二：消费者（consumer）

（一）生产者 (创建一个rabbit_publisher.php的文件)

**创建连接-->创建channel-->创建交换机对象-->发送消息**

 

    $conn_args = array(
        'host' => '127.0.0.1',
        'port' => '5672',
        'login' => 'guest',
        'password' => 'guest',
        'vhost'=>'/'
    );
    
    //创建连接和channel
    $conn = new AMQPConnection($conn_args);
    if (!$conn->connect()) {
        die("Cannot connect to the broker!\n");
    }
    $channel = new AMQPChannel($conn);
    
    //创建交换机
    $e_name = 'e_linvo'; //交换机名
    $ex = new AMQPExchange($channel);
    $ex->setName($e_name);
    $ex->setType(AMQP_EX_TYPE_DIRECT); //direct类型
    $ex->setFlags(AMQP_DURABLE); //持久化
    echo "Exchange Status:".$ex->declare()."\n";
    
    
    echo "Send Message:".$ex->publish("TEST MESSAGE，key_1 by xust" . date('H:i:s', time()), 'key_1')."\n";
    echo "Send Message:".$ex->publish("TEST MESSAGE，key_2 by xust" . date('H:i:s', time()), 'key_2')."\n";

（二）消费者(创建一个rabbit_consumer.php的文件)

**创建连接-->创建channel-->创建交换机-->创建队列-->绑定交换机/队列/路由键-->接收消息**

 

    $conn_args = array(
        'host' => '127.0.0.1',
        'port' => '5672',
        'login' => 'guest',
        'password' => 'guest',
        'vhost'=>'/'
    );
    
    $e_name = 'e_linvo'; //交换机名
    $q_name = 'q_linvo'; //队列名
    $k_route = 'key_2'; //路由key
    
    //创建连接和channel
    $conn = new AMQPConnection($conn_args);
    if (!$conn->connect()) {
        die("Cannot connect to the broker!\n");
    }
    $channel = new AMQPChannel($conn);
    
    //创建交换机
    $ex = new AMQPExchange($channel);
    $ex->setName($e_name);
    $ex->setType(AMQP_EX_TYPE_DIRECT); //direct类型
    $ex->setFlags(AMQP_DURABLE); //持久化
    echo "Exchange Status:".$ex->declare()."\n";
    
    //创建队列
    $q = new AMQPQueue($channel);
    $q->setName($q_name);
    $q->setFlags(AMQP_DURABLE); //持久化
    
    //绑定交换机与队列，并指定路由键
    echo 'Queue Bind: '.$q->bind($e_name, 'key_2')."\n";　
    
    //阻塞模式接收消息
    echo "Message:\n";
    $q->consume('processMessage', AMQP_AUTOACK); //自动ACK应答
    
    $conn->disconnect();
    
    /**
     * 消费回调函数
     * 处理消息
     */
    function processMessage($envelope, $queue) {
        var_dump($envelope->getRoutingKey);
        $msg = $envelope->getBody();
        echo $msg."\n"; //处理消息
    }

执行两个文件，再打开RabbitMQ的管理中心 [http://127.0.0.1:15672/][11]

![][12]

说明你的程序运行是正常的。

如果有什么地方说错了，望，批评指正！谢谢。



[0]: http://www.cnblogs.com/bluebirds/p/6068927.html
[1]: http://www.erlang.org/download.html
[2]: http://www.rabbitmq.com/download.html
[3]: http://images2015.cnblogs.com/blog/1062001/201611/1062001-20161116114224107-1039140680.png
[4]: http://192.168.16.16:15672/
[5]: http://images2015.cnblogs.com/blog/1062001/201611/1062001-20161116114448748-197993388.png
[6]: http://www.cnblogs.com/bluebirds/p/6069524.html
[7]: http://images2015.cnblogs.com/blog/1062001/201611/1062001-20161116144033326-1296671376.png
[8]: http://images2015.cnblogs.com/blog/1062001/201611/1062001-20161116144257717-568625412.png
[9]: https://pecl.php.net/package/amqp
[10]: http://www.cnblogs.com/bluebirds/p/6069623.html
[11]: http://127.0.0.1:15672/
[12]: http://images2015.cnblogs.com/blog/1062001/201611/1062001-20161116150719967-850715582.png