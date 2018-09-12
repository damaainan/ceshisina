# PHPsocket通信 

[2015-11-19][0]socket通信的思路是

> server创建一个socket->server端对端口进行监听->write和read->close

> client创建一个socket->连接到server的IP/Port->write和read消息->close

下面是server端的代码
```php
set_time_limit ( 0 );
$socket = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP );
socket_bind ( $socket, '127.0.0.1', 1995 );
socket_listen ( $socket );
$counter = 0;

while ( 1 ) {
    if (($connection = socket_accept ( $socket )) < 0) {
        echo "socket_accept failed:reason:" . socket_strerror ( $connection ) . PHP_EOL;
    } else {
        $msg = 'server的消息' . PHP_EOL;
        socket_write ( $connection, $msg, strlen ( $msg ) );
        echo "server接收socket成功" . PHP_EOL;
        $read = socket_read ( $connection, 1024 );
        echo "收到的信息：" . $read . PHP_EOL;
        if (++ $counter >= 10) {
            break;
        }
    }
    socket_close ( $connection );
}
```

然后是client端的代码
```php
// client端
$socket = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP );
if ($socket < 0) {
    echo socket_strerror ( $socket );
    exit ();
} else {
    echo "创建socket成功\n";
}
$connection = socket_connect ( $socket, '127.0.0.1', 1995 );
if ($connection < 0) {
    echo socket_strerror ( $connection );
    exit ();
} else {
    echo "连接socket成功\n";
}
$in = "来自client的消息\n";
if (! socket_write ( $socket, $in, strlen ( $in ) )) {
    echo socket_strerror ( $socket );
    exit ();
} else {
    echo "write成功\n";
}
while ( $out = socket_read ( $socket, 1024 ) ) {
    echo "接受消息成功\n";
    echo $out;
}
socket_close ( $socket );
echo "关闭socket成功\n";
```

效果图：

![效果图][1]

[0]: https://www.jwlchina.cn/2015/11/19/PHPsocket通信/
[1]: http://cl.ly/image/2041270g452m/QQ%E6%88%AA%E5%9B%BE20151115194608.png