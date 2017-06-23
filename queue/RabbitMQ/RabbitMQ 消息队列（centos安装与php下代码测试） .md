## RabbitMQ 消息队列（centos安装与php下代码测试） 


[拍簧片的小伙伴][1]

消息、队列和交换器是构成AMQP的三个关键组件，任何一个组件的实效都会导致信息通信的中断；

以上主要介绍构成AMQP的三个关键要素，那么它们之间是如何工作的呢？

![][5]

由图中可以看出，交换器接收发送端应用程序的消息，通过设定的路由转发表与绑定规则将消息转发至相匹配的消息队列，消息队列继而将接收到的消息转发至对应的接收端应用程序。数据通信网络通过IP地址形成的路由表实现IP报文的转发，在AMQP环境中的通信机制也非常类似，交换器通过AMQP消息头（Header）中的路由选择关键字（Routing Key）而形成的绑定规则（Binding）来实现消息的转发，也就是说，“绑定”即连接交换机与消息队列的路由表。消息生产者发送的消息中所带有的Routing Key是交换器转发的判断因素，也就是AMQP中的“IP地址”，交换器获取消息之后提取Routing Key触发路由，通过绑定规则将消息转发至相应队列，消息消费者最后从队列中获取消息。

**1、安装Erlang**

> 下载地址：[http://pan.baidu.com/s/1o7MZiEA][6]

    tar zxvf otp_src_19.1.tar.gz

    cd otp_src_19.1

    ./configure --prefix=/usr/local/erlang --with-ssl --enable-threads --enable-smp-support --enable-kernel-poll --enable-hipe --without-javac

    make && make install

**2、安装RabbitMQ**

> 下载地址：[http://pan.baidu.com/s/1jIFVWrG][7]

    xz -d rabbitmq-server-generic-unix-3.6.5.tar.xz

    tar xvf rabbitmq-server-generic-unix-3.6.5.tar

    mv rabbitmq_server-3.6.5 rabbitmq

**3、启动RabbitMQ**

    #启动rabbitmq服务
    /usr/local/rabbitmq/sbin/rabbitmq-server
    #后台启动
    /usr/local/rabbitmq/sbin/rabbitmq-server -detached
    #关闭rabbitmq服务
    /usr/local/rabbitmq/sbin/rabbitmqctl stop
    或
    ps -ef | grep rabbit 和 kill -9 xxx
    #开启插件管理页面
    /usr/local/rabbitmq/sbin/rabbitmq-plugins enable rabbitmq_management
    #创建用户
    /usr/local/rabbitmq/sbin/rabbitmqctl add_user rabbitadmin 123456
    /usr/local/rabbitmq/sbin/rabbitmqctl set_user_tags rabbitadmin administrator
    #WEB登录
    http://127.0.0.1:15672
    用户名 ： rabbitadmin 密码 ： 123456
  

> 注意：无法访问可能是防火墙未关闭

**4、缺少**librabbitmq （ 需要安装rabbitmq-c，rabbitmq-c是一个用于[C语言][8]的，与AMQP server进行交互的client库。）

> 第一步 ：执行命令-> git clone git://github.com/alanxz/rabbitmq-c.git

> 第二部 ：进入该目录下 执行命令-> cd rabbitmq-c

> 第三步 ：执行命令->./configure --prefix=/usr/local/rabbitmq-c-0.4.1

> 第四步 ：执行命令-> make && make install

**5、安装amqp**

> 第一步 ：cd amqp-1.2.0 phpize

> 第二步：./configure --with-php-config=/usr/bin/php-config --with-amqp --with-librabbitmq-dir=/usr/local/rabbitmq-c-0.4.1/

> 第三步 ：执行命令->make && make install (make clean)

> 第四步 ：php.ini 添加 extension=amqp.so

执行测试 

task.php

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

work.php

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
    /*//创建交换机
    $exchange = new AMQPExchange($channel);
    $exchange->setName('test1');
    $exchange->setType(AMQP_EX_TYPE_DIRECT);
    $exchange->declare();*/
    
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

AMQP定义三种不同类型的交换器：广播式交换器（Fanout Exchange）、直接式交换器（Direct Exchange）和主题式交换器（Topic Exchange），三种交换器实现的绑定规则也有所不同。

**介绍一下[RabbitMQ 三种Exchange][9]**

>** Direct Exchange**  – 处理路由键。需要将一个队列绑定到交换机上，要求该消息与一个特定的路由键完全匹配。这是一个完整的匹配。如果一个队列绑定到该交换机上要求路由键 “dog”，则只有被标记为“dog”的消息才被转发，不会转发dog.puppy，也不会转发dog.guard，只会转发dog。   
  
![][10]

>** Fanout Exchange**  – 不处理路由键。你只需要简单的将队列绑定到交换机上。一个发送到交换机的消息都会被转发到与该交换机绑定的所有队列上。很像子网广播，每台子网内的主机都获得了一份复制的消息。Fanout交换机转发消息是最快的。   
  
![][11]

>** Topic Exchange** – 将路由键和某模式进行匹配。此时队列需要绑定要一个模式上。符号“#”匹配一个或多个词，符号“*”匹配不多不少一个词。因此“audit.#”能够匹配到“audit.irs.corporate”，但是“audit.*” 只会匹配到“audit.irs”。我在RedHat的朋友做了一张不错的图，来表明topic交换机是如何工作的：   


![][12]


[1]: https://my.oschina.net/wangjie404/home
[2]: #comment-list
[3]: https://developer.ibm.com/sso/bmregistration?lang=zh_CN&ca=dwchina-_-bluemix-_-OSCHINA-_-onlineeventQ22017
[4]: https://my.oschina.net/img/hot3.png
[5]: ./img/16140036_fL0Q.jpg
[6]: http://pan.baidu.com/s/1o7MZiEA
[7]: http://pan.baidu.com/s/1jIFVWrG
[8]: http://lib.csdn.net/base/c
[9]: http://melin.iteye.com/blog/691265
[10]: ./img/16135659_arJ2.png
[11]: ./img/16135659_ogNG.png
[12]: ./img/16135659_FkGV.png