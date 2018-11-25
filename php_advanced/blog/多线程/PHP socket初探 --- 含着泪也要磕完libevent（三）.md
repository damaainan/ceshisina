## PHP socket初探 --- 含着泪也要磕完libevent（三）

来源：[https://segmentfault.com/a/1190000017071175](https://segmentfault.com/a/1190000017071175)

原文地址：[https://t.ti-node.com/thread/...][8]

这段时间相比大家也看到了，本人离职了，一是在家偷懒实在懒得动手，二是好不容易想写点儿时间全部砸到数据结构和算法那里了。

今儿回过头来，继续这里的文章。那句话是怎么说的：

“ **`自己选择的课题，含着泪也得磕完！`** ”（图文无关，[详情点击这里][9]）。

![][0]

其实在上一篇libevent文章中（[《PHP socket初探 --- 硬着头皮继续libevent（二）》][10]），如果你总结能力很好的话，可以观察出来我们尝试利用libevent做了至少两件事情：


* 毫秒级别定时器
* 信号监听工具


大家都是码php的，也喜欢把自己说的洋气点儿：“ 我是写服务器的 ”。所以，今天的第一个案例就是拿libevent来构建一个简单粗暴的http服务器：

```php
<?php
$host = '0.0.0.0';
$port = 9999;
$listen_socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
socket_bind( $listen_socket, $host, $port );
socket_listen( $listen_socket );

echo PHP_EOL.PHP_EOL."Http Server ON : http://{$host}:{$port}".PHP_EOL;

// 将服务器设置为非阻塞，此处概念可能略拐弯，建议各位查阅一下手册
socket_set_nonblock( $listen_socket );
// 创建事件基础体，还记得航空母舰吗？
$event_base = new EventBase();
// 创建一个事件，还记得歼15舰载机吗？我们将“监听socket”添加到事件监听中，触发条件是read，也就是说，一旦“监听socket”上有客户端来连接，就会触发这里，我们在回调函数里来处理接受到新请求后的反应
$event = new Event( $event_base, $listen_socket, Event::READ | Event::PERSIST, function( $listen_socket ){
  // 为什么写成这样比较执拗的方式？因为，“监听socket”已经被设置成了非阻塞，这种情况下，accept是立即返回的，所以，必须通过判定accept的结果是否为true来执行后面的代码。一些实现里，包括workerman在内，可能是使用@符号来压制错误，个人不太建议这>样做
  if( ( $connect_socket = socket_accept( $listen_socket ) ) != false){
    echo "有新的客户端：".intval( $connect_socket ).PHP_EOL;
    $msg = "HTTP/1.0 200 OK\r\nContent-Length: 2\r\n\r\nHi";
    socket_write( $connect_socket, $msg, strlen( $msg ) );
    socket_close( $connect_socket );
  }
}, $listen_socket );
$event->add();
$event_base->loop();

```

将代码保存为test.php，然后php http.php运行起来。再开一个终端，使用curl的GET方式去请求服务器，效果如下：

![][1]

这是一个非常非常简单地不能再简单的http demo了，对于一个完整的http服务器而言，他还差比较完整的http协议的实现、多核CPU的利用等等。这些，我们会放到后面继续深入的文章中开始细化丰富。

还记得我们使用select系统调用实现了一个粗暴的在线聊天室，select这种业余的都敢出来混个聊天室，专业的绝对不能怂。

无数个专业???????????????送给libevent！

![][2]

![][3]

![][4]

啦啦啦啦，开始码：

```php
<?php
$host = '0.0.0.0';
$port = 9999;
$fd = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
socket_bind( $fd, $host, $port );
socket_listen( $fd );
// 注意，将“监听socket”设置为非阻塞模式
socket_set_nonblock( $fd );

// 这里值得注意，我们声明两个数组用来保存 事件 和 连接socket
$event_arr = []; 
$conn_arr = []; 

echo PHP_EOL.PHP_EOL."欢迎来到ti-chat聊天室!发言注意遵守当地法律法规!".PHP_EOL;
echo "        tcp://{$host}:{$port}".PHP_EOL;

$event_base = new EventBase();
$event = new Event( $event_base, $fd, Event::READ | Event::PERSIST, function( $fd ){
  // 使用全局的event_arr 和 conn_arr
  global $event_arr,$conn_arr,$event_base;
  // 非阻塞模式下，注意accpet的写法会稍微特殊一些。如果不想这么写，请往前面添加@符号，不过不建议这种写法
  if( ( $conn = socket_accept( $fd ) ) != false ){
    echo date('Y-m-d H:i:s').'：欢迎'.intval( $conn ).'来到聊天室'.PHP_EOL;
    // 将连接socket也设置为非阻塞模式
    socket_set_nonblock( $conn );
    // 此处值得注意，我们需要将连接socket保存到数组中去
    $conn_arr[ intval( $conn ) ] = $conn;
    $event = new Event( $event_base, $conn, Event::READ | Event::PERSIST, function( $conn ) use( $event_arr ) { 
      global $conn_arr;
      $buffer = socket_read( $conn, 65535 );
      foreach( $conn_arr as $conn_key => $conn_item ){
        if( $conn != $conn_item ){
          $msg = intval( $conn ).'说 : '.$buffer;
          socket_write( $conn_item, $msg, strlen( $msg ) );
        }   
      }   
    }, $conn );
    $event->add();
    // 此处值得注意，我们需要将事件本身存储到全局数组中，如果不保存，连接会话会丢失，也就是说服务端和客户端将无法保持持久会话
    $event_arr[ intval( $conn ) ] = $event;
  }
}, $fd );
$event->add();
$event_base->loop();
```

将代码保存为server.php，然后php server.php运行，再打开其他三个终端使用telnet连接上聊天室，运行效果如下所示：

![][5]

尝试放一张动态图试试，看看行不行，自己制作的gif都特别大，不知道带宽够不够。

![][6]

截止到这篇为止，死磕Libevent系列的大体核心三把斧就算是抡完了，弄完这些，你在遇到这些代码的时候，就应该不会像下面这个样子了：

![][7]

[8]: https://t.ti-node.com/thread/6445811932061499392
[9]: https://tieba.baidu.com/p/3504775033?red_tag=1379561293
[10]: https://blog.ti-node.com/blog/6396317917192912897
[0]: https://segmentfault.com/img/remote/1460000017071178
[1]: https://segmentfault.com/img/remote/1460000017071179/view?w=917&h=364
[2]: https://segmentfault.com/img/remote/1460000017071180
[3]: https://segmentfault.com/img/remote/1460000017071181
[4]: https://segmentfault.com/img/remote/1460000017071182/view?w=1060&h=553
[5]: https://segmentfault.com/img/remote/1460000017071183
[6]: https://segmentfault.com/img/remote/1460000017071184/view?w=480&h=267
[7]: https://segmentfault.com/img/remote/1460000017071185/view?w=480&h=480