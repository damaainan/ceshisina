## RabbitMQ队列使用 （PHP） 


[拍簧片的小伙伴][1]



摘要: Ubuntu下 PHP7中RabbitMQ的安装 和 使用例子 

首先说明下，安装了好久都没成功，才发现我的php版本是7 的 ，最后使用的

    https://github.com/pdezwart/php-amqp

编译才成功！不说了 说多了都是眼泪；网上找了好多安装教程发现都没啥用；最后自己搞了一个出来，

直接上代码吧希望对大家有帮助 ………………

本文介绍了在Linux下给PHP安装amqp扩展的过程和如何使用消息队列，有需要的朋友可以关注一下。

1,binding key和routing key

binding key和routing key是都不过是自己设置的一组字符,只是用的地方不同,binding key是在绑定交换机和队列时候通过方法传递的字符串,routing key是在发布消息时候,顺便带上的字符串,有些人说这两个其实是一个东西,也对也不对,说对,是因为这两个可以完全一样,说不对,是因为这两个起的作用不同,一个交换机可以绑定很多队列,但是每个队列也许需要的消息类型不同,binding key就是这个绑定时候留在交换机和队列之间的提示信息,当消息发送出来后,随着消息一起发送的routing key如果和binding key一样就说明消息是这个队列要的东西,如果不一样那就不要给这个队列,交换机你找找下个队列看看要不要.明白了吧,这两个key就是暗号,对上了就是自己人,对不上那麻烦你再找找去.

binding key和routing key的配对其实也不是就要完全一样,还可以'相似'配对,建立交换机的时候,就要告诉MQ,我要声明的这个交换机和它上面的队列之间传输消息时候要求routing key和binding key完全一样,这种模式叫Direct,如果routing key和binding key可以'模糊'匹配,这种模式叫Topic,如果不需要匹配,尽管发,叫Fanout.

2,持久化

交换机和队列都可以在创建时候设置为持久化,重启以后会回复,但是其中的消息未不会,如果要消息也恢复,将消息发布到交换机的时候，可以指定一个标志“Delivery Mode”（投递模式）, 1为非持久化,2为持久化.

3,流控机制

当消息生产的速度更快,而进程的处理能力低时,消息就会堆积起来,占用内存越来越多,导致MQ崩溃,所以rabbitmq有一个流控机制,当超过限定时候就会阻止接受消息,mq流控有三种机制

1,主动阻塞住发消息太快的连接,这个无法调整,如果被阻塞了,在abbitmqctl 控制台上会显示一个blocked的状态。

2,内存超过限量,会阻塞连接,在vm_memory_high_watermark可调

3,剩余磁盘在限定以下mq会 主动阻塞所有的生产者,默认为50m,在disk_free_limit可调.

RabbitMQ是一个消息代理。它的核心原理非常简单：接收和发送消息。你可以把它想像成一个邮局：你把信件放入邮箱，邮递员就会把信件投递到你的收件人处。在这个比喻中，RabbitMQ是一个邮箱、邮局、邮递员。RabbitMQ和邮局的主要区别是，它处理的不是纸，而是接收、存储和发送二进制的数据——_消息_。一般提到RabbitMQ和消息，都用到一些专有名词。

* _生产(Producing)_意思就是发送。发送消息的程序就是一个_生产者(producer)_。我们一般用”P”来表示：
* _队列(queue)_就是邮箱的名称。消息通过你的应用程序和RabbitMQ进行传输，它们能够只存储在一个_队列（queue）_中。 _队列（queue）_没有任何限制，你要存储多少消息都可以——基本上是一个无限的缓冲。多个_生产者（producers）_能够把消息发送给同一个队列，同样，多个_消费者（consumers）_也能攻从一个_队列（queue）_中获取数据。队列可以化城这样（图上是队列的名称）：
* _消费（Consuming）_和获取消息是一样的意思。一个_消费者（consumer）_就是一个等待获取消息的程序。我们把它画作”C”，下面先介绍如何安装：

#### 步骤1.安装rabbitmq server

执行命令-> sudo apt-get install rabbitmq-server

#### 步骤2.安装librabbitmq-c和rabbitmq-codegen

下载RabbitMQ 对应的源码

第一步 ：执行命令-> git clone git://github.com/alanxz/rabbitmq-c.git  
第二部 ：进入该目录下 执行命令-> cd rabbitmq-c  
第三步 ：执行命令-> git submodule init （如果提示需要依赖什么东西，直接执行第四步或者直接将那个 提示东西下 载下来就可以了）  
第四步 ：执行命令-> git submodule update  
第五步 ：执行命令-> sudo autoreconf -i && ./configure && make && sudo make install

#### 步骤3：下面安装php-amqp

    下面安装php-amqp

第一步 ：执行命令-> git clone https://github.com/pdezwart/php-amqp  
第二部 ：进入该目录下 执行命令-> cd php-amqp  
第三步 ：执行命令-> phpize   
第四步 ：执行命令-> ./configure --with-php-config=/usr/local/php/bin/php-config --with-amqp   
第五步 ：执行命令-> make && sudo make install

    root@iZ2ze6lj061gs2debtnitsZ:/home/rabbitmq/rabbitmq-c# git clone https://github.com/pdezwart/php-amqp.git
    Cloning into 'php-amqp'...
    remote: Counting objects: 2084, done.
    remote: Total 2084 (delta 0), reused 0 (delta 0), pack-reused 2084
    Receiving objects: 100% (2084/2084), 937.67 KiB | 18.00 KiB/s, done.
    Resolving deltas: 100% (1371/1371), done.
    Checking connectivity... done.
    root@iZ2ze6lj061gs2debtnitsZ:/home/rabbitmq/rabbitmq-c# cd php-amqp/
    root@iZ2ze6lj061gs2debtnitsZ:/home/rabbitmq/rabbitmq-c/php-amqp# phpize
    Configuring for:
    PHP Api Version:         20151012
    Zend Module Api No:      20151012
    Zend Extension Api No:   320151012
    root@iZ2ze6lj061gs2debtnitsZ:/home/rabbitmq/rabbitmq-c/php-amqp# ./configure --with-php-config=/usr/local/php/bin/php-config --with-amqp && make && sudo make install
    checking for grep that handles long lines and -e... /bin/grep
    checking for egrep... /bin/grep -E
    checking for a sed that does not truncate output... /bin/sed
    

如果上面步骤都执行成功了那么

记得在php.ini中加入amqp扩展：

    extension=amqp.so

重启 php 

    执行命令->/etc/init.d/php-fpm restart  使用 php -m | grep amqp 查看是否存在

或者 查看phpinfo 是否存在这个扩展 ;到此就安装成功了! 查看服务是否启动

    root@iZ2ze6lj061gs2debtnitsZ:/home/wwwroot/default# ps -e | grep rabbitmq-server
     9293 ?        00:00:00 rabbitmq-server
    

## 安装过程中可能会遇到的问题

1、缺少libtool包

    configure.ac: installing ./install-sh
    configure.ac: installing ./missing
    configure.ac:34: installing ./config.guess
    configure.ac:34: installing ./config.sub
    Makefile.am:3: Libtool library used but LIBTOOL is undefined
    Makefile.am:3:
    Makefile.am:3: The usual way to define LIBTOOL is to add AC_PROG_LIBTOOL
    Makefile.am:3: to configure.ac and run aclocal and autoconf again.
    Makefile.am: C objects in subdir but AM_PROG_CC_C_O not in configure.ac
    Makefile.am: installing ./compile
    Makefile.am: installing ./depcomp
    autoreconf: automake failed with exit status: 1

解决办法，安装libtool，ubuntu：

    sudo apt-get install libtool

接下来介绍一下

生产者（Producer）：把消息发送到一个名为“hello”的队列中。

消费者（consumer）：从这个队列中获取消息

先给大家写一个demo 看看情况吧 ！

第一步建立一个到RabbitMQ服务器的连接 

    <?php
    //配置信息
    $conn_args = array(
        'host' => '127.0.0.1',
        'port' => '5672',
        'login' => 'guest',
        'password' => 'guest',
        'vhost' => '/'
    );
    //首先要做的事情就是建立一个到RabbitMQ服务器的连接。
    $conn = new AMQPConnection($conn_args);
    if (!$conn->connect()) {
        die('Not connected ' . PHP_EOL);
    }
    /**
     * 现在我们已经连接上服务器了，
     * 那么，在发送消息之前我们需要确认队列是存在的。
     * 如果我们把消息发送到一个不存在的队列，RabbitMQ会丢弃这条消息。
     * 我门先创建一个名为hello的队列，然后把消息发送到这个队列中。
     */
    $queueName = 'hello';
    $channel = new AMQPChannel($conn);
    $exchange = new AMQPExchange($channel);
    /**
     * 这时候我们就可以发送消息了，我们第一条消息只包含了 Hello World!字符串，我们打算把它发送到我们的hello队列。
     * 在RabbitMQ中，消息是不能直接发送到队列，它需要发送到交换器（exchange）中
     * 现在我们所需要了解的是如何使用默认的交换器（exchange），它使用一个空字符串来标识。
     * 交换器允许我们指定某条消息需要投递到哪个队列，
     * $routeKey参数必须指定为队列的名称：publish（message,$routekey）
     */
    
    $queue = new AMQPQueue($channel);
    $queue->setName($queueName);
    //我们需要确认队列是存在的。使用$queue->declare()创建一个队列——我们可以运行这个命令很多次，但是只有一个队列会创建。
    $queue->declare();
    $message = [
        'name' => 'hello',
        'args' => ["0", "1", "2", "3"],
    ];
    //生产者，向RabbitMQ发送消息
    $state = $exchange->publish(json_encode($message), 'hello');
    if (!$state) {
        echo 'Message not sent', PHP_EOL;
    } else {
        echo 'Message sent!', PHP_EOL;
    }
    /**
     * 这里就在这个页面获取了 ；
     * 或者可以自己定义一个received.php来接受生产者发送的消息（死循环，有消息就接受）
     */
    //消费者获得消息内容
    while ($envelope = $queue->get(AMQP_AUTOACK)) {
        echo ($envelope->isRedelivery()) ? 'Redelivery' : 'New Message';
        echo PHP_EOL;
        echo $envelope->getBody(), PHP_EOL;
    }
    
    ?>

接下来：

_任务队列（Task Queues）_

_是为了避免等待一些占用大量资源、时间的操作。_当我们把_任务（Task）_当作消息发送到队列中，一个运行在后台的工作者（worker）进程就会取出任务然后处理。当你运行多个工作者（workers），任务就会在它们之间共享

这个概念在网络应用中是非常有用的，它可以在短暂的HTTP请求中处理一些复杂的任务。

使用工作队列的一个好处就是它能够并行的处理队列。如果堆积了很多任务，我们只需要添加更多的工作者（workers）就可以了

当处理一个比较耗时得任务的时候，你也许想知道消费者（consumers）是否运行到一半就挂掉。当前的代码中，当消息被RabbitMQ发送给 消费者（consumers）之后，马上就会在内存中移除。这种情况，你只要把一个工作者（worker）停止，正在处理的消息就会丢失。同时，所有发送 到这个工作者的还没有处理的消息都会丢失。

我们不想丢失任何任务消息。如果一个工作者（worker）挂掉了，我们希望任务会重新发送给其他的工作者（worker）。

为了防止消息丢失，RabbitMQ提供了消息_响应（ack）_。消费者会通过一个ack（响应），告诉RabbitMQ已经收到并处理了某条消息，然后RabbitMQ就会释放并删除这条消息。

如果消费者（consumer）挂掉了，没有发送响应，RabbitMQ就会认为消息没有被完全处理，然后重新发送给其他消费者（consumer）。这样，及时工作者（workers）偶尔的挂掉，也不会丢失消息。

消息是没有超时这个概念的；当工作者与它断开连的时候，RabbitMQ会重新发送消息。这样在处理一个耗时非常长的消息任务的时候就不会出问题了。

不说那么多直接上代码吧 

task.php 发布任务

    <?php
    $data = [
        'name' => 'hello',
        'args' => ["01", "1", "2", "3"]];
    $message = empty($argv[1]) ? json_encode($data) : ' ' . $argv[1];
    
    //配置信息
    $conn_args = array(
        'host' => '127.0.0.1',
        'port' => '5672',
        'login' => 'guest',
        'password' => 'guest',
        'vhost' => '/'
    );
    //首先要做的事情就是建立一个到RabbitMQ服务器的连接。
    $connection = new AMQPConnection($conn_args);
    if (!$connection->connect()) {
        die('Not connected ' . PHP_EOL);
    }
    
    $channel = new AMQPChannel($connection);
    $exchange = new AMQPExchange($channel);
    $exchange->setName('test');
    $queue = new AMQPQueue($channel);
    $queue->setName('task_queue1');
    //首先，为了不让队列丢失，需要把它声明为持久化（durable）：
    $queue->setFlags(AMQP_DURABLE);
    $queue->declare();
    
    $exchange->publish($message, 'task_queue1');
    var_dump("message: $message");
    
    $connection->disconnect();

worker.php 处理任务

    <?php
    $connection = new AMQPConnection([
        'host' => '127.0.0.1',
        'port' => '5672',
        'vhost' => '/',
        'login' => 'guest',
        'password' => 'guest'
    ]);
    $connection->connect() or die("Cannot connect to the broker!\n");
    $channel = new AMQPChannel($connection);
    //创建交换机
    $exchange = new AMQPExchange($channel);
    $exchange->setName('test');
    $exchange->setType(AMQP_EX_TYPE_DIRECT);
    $exchange->declare();
    //创建消息队列
    $queue = new AMQPQueue($channel);
    $queue->setName('task_queue1');
    //为了确保信息不会丢失，有两个事情是需要注意的：我们必须把“队列”和“消息”设为持久化。
    //首先，为了不让队列丢失，需要把它声明为持久化（durable）：
    $queue->setFlags(AMQP_DURABLE);
    $queue->declare();
    $queue->bind('test', 'task_queue1');
    
    var_dump('[*] Waiting for messages. To exit press CTRL+C');
    //消费者接受消息
    while (true) {
        $queue->consume('callback');
        /**
         * 我们可以使用$channel->qos();方法，并设置prefetch_count=1。
         * 这样是告诉RabbitMQ，再同一时刻，不要发送超过1条消息给一个工作者（worker），
         * 直到它已经处理了上一条消息并且作出了响应。这样，RabbitMQ就会把消息分发给下一个空闲的工作者（worker）
         */
        $channel->qos(0, 1);
    }
    $connection->disconnect();
    function callback($envelope, $queue)
    {
        $msg = $envelope->getBody();
        var_dump("Received:" . $msg);
        sleep(substr_count($msg, '.'));
        //$queue->ack()。当工作者（worker）完成了任务，就发送一个响应。
        //当工作者（worker）挂掉这后，所有没有响应的消息都会重新发送。
        $queue->ack($envelope->getDeliveryTag());
    }

运行结果:

task.php

    root@iZ2ze6lj061gs2debtnitsZ:/home/wwwroot/default/test# php task.php task1
        string(15) "[x] Sent  task1"
    root@iZ2ze6lj061gs2debtnitsZ:/home/wwwroot/default/test# php task.php task2
        string(15) "[x] Sent  task2"
    root@iZ2ze6lj061gs2debtnitsZ:/home/wwwroot/default/test# php task.php task3
        string(15) "[x] Sent  task3"
    root@iZ2ze6lj061gs2debtnitsZ:/home/wwwroot/default/test# php task.php task4
        string(15) "[x] Sent  task4"
    

worker.php

shell - 1

    root@iZ2ze6lj061gs2debtnitsZ:/home/wwwroot/default/test# php worker.php 
    string(46) "[*] Waiting for messages. To exit press CTRL+C"
    string(20) " [x] Received: task2"
    string(20) " [x] Received: task4"
    

shell - 2 

    root@iZ2ze6lj061gs2debtnitsZ:/home/wwwroot/default/test# php worker.php 
    string(46) "[*] Waiting for messages. To exit press CTRL+C"
    string(20) " [x] Received: task1"
    string(20) " [x] Received: task3"
    

先写到这里吧 ；

欢迎留言相互讨论！

附 ： php 中amq 文档地址：http://php.net/manual/pl/amqpconnection.connect.php


[1]: https://my.oschina.net/wangjie404/home
[2]: #comment-list
[3]: https://developer.ibm.com/sso/bmregistration?lang=zh_CN&ca=dwchina-_-bluemix-_-OSCHINA-_-onlineeventQ22017
[4]: https://my.oschina.net/img/hot3.png