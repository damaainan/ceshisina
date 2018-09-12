## PHP socket初探 --- select系统调用

来源：[https://segmentfault.com/a/1190000016237660](https://segmentfault.com/a/1190000016237660)

[原文地址：[https://blog.ti-node.com/blog...][1]]

在＜[PHP socket初探 --- 先从一个简单的socket服务器开始][2]＞中依次讲解了三个逐渐进步的服务器：

* 只能服务于一个客户端的服务器
* 利用fork可以服务于多个客户端的额服务器
* 利用预fork派生进程服务于多个客户端的服务器


最后一种服务器的进程模型基本上的大概原理其实跟我们常用的apache是非常相似的．
其实这种模型最大的问题在于需要根据实际业务预估进程数量，依旧是需要大量进程来解决问题，可能会出现CPU浪费在进程间切换上，还有可能会出现惊群现象（简单理解就是100个进程在等带客户端连接，来了一个客户端但是所有进程都被唤醒了，但最终只有一个进程为这个客户端服务，其余99个白白折腾），那么，有没有一种解决方案可以使得少量进程服务于多个客户端呢？
答案就是在＜[PHP socket初探 --- 关于IO的一些枯燥理论][3]＞中提到的＂IO多路复用＂．多路是指多个客户端连接socket，复用就是指复用少数几个进程，多路复用本身依然隶属于同步通信方式，只是表现出的结果看起来像异步，这点值得注意．目前多路复用有三种常用的方案，依次是：

* select，最早的解决方案
* poll，算是select的升级版
* epoll，目前的最终解决版，解决c10k问题的功臣


今天说的是select，这个东西本身是个Linux系统调用．在Linux中一切皆为文件，socket也不例外，每当Linux打开一个文件系统都会返回一个对应该文件的标记叫做文件描述符．文件描述符是一个非负整数，当文件描述数达到最大的时候，会重新回到小数重新开始（题外话：按照传统，一般情况下标准输入是0，标准输出是1，标准错误是2）．对文件的读写操作就是利用对文件描述符的读写操作．一个进程可以操作的文件描述符的数量是有限制的，不同系统有不同的数量，在linux中，可以通过调整ulimit来调整控制．
先通过一个简单的例子说明下select的作用和功能．双11到了，你给少林足球队买了很多很多球鞋，分别有10个快递给你运送，然后你就不断地电话询问这10个快递员，你觉得有点儿累．阿梅很心疼你，于是阿梅就说："这事儿你不用管了，你去专心练大力金刚腿吧，等任何一个快递到了，我告诉你"．当其中一个快递来了后，阿梅就喊你：＂下来啦，有快递！＂，但是，这个阿梅比较缺心眼，她不告诉你是具体哪双鞋子的快递，只告诉你有快递到了．所以，你只能依次查询一遍所有快递单的状态才能确认是哪个签收了．
上面这个例子通过结合术语演绎一遍就是，你就是服务器软件，阿梅就是select，10个快递就是10个客户端（也就是10个连接socket fd）．阿梅负责替你管理着这10个连接socket fd，当其中任何一个fd有反应了也就是可以读数据或可以发送数据了，阿梅（select）就会告诉你有可以读写的fd了，但是阿梅（select）不会告诉你是哪个fd可读写，所以你必须轮循所有fd来看看是哪个fd，是可读还是可写．
是时候机械记忆一波儿了：
当你启动select后，需要将三组不同的socket fd加入到作为select的参数，传统意义上这种fd的集合就叫做fd_set，三组fd_set依次是可读集合，可写集合，异常集合．三组fd_set由系统内核来维护，每当select监控管理的三个fd_set中有可读或者可写或者异常出现的时候，就会通知调用方．调用方调用select后，调用方就会被select阻塞，等待可读可写等事件的发生．一旦有了可读可写或者异常发生，需要将三个fd_set从内核态全部copy到用户态中，然后调用方通过轮询的方式遍历所有fd，从中取出可读可写或者异常的fd并作出相应操作．如果某次调用方没有理会某个可操作的fd，那么下一次其余fd可操作时，也会再次将上次调用方未处理的fd继续返回给调用方，也就是说去遍历fd的时候，未理会的fd依然是可读可写等状态，一直到调用方理会．
上面都是我个人的理解和汇总，有错误可以指出，希望不会误人子弟．下面通过php代码实例来操作一波儿select系统调用．在php中，你可以通过stream_select或者socket_select来操作select系统调用，下面演示socket_select进行代码演示：

```php
<?php

// BEGIN 创建一个tcp socket服务器
$host          = '0.0.0.0';
$port          = 9999;
$listen_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($listen_socket, $host, $port);
socket_listen($listen_socket);
// END 创建服务器完毕

// 也将监听socket放入到read fd set中去，因为select也要监听listen_socket上发生事件
$client = [$listen_socket];
// 先暂时只引入读事件，避免有同学晕头
$write = [];
$exp   = [];

// 开始进入循环
while (true) {
    $read = $client;
    // 当select监听到了fd变化，注意第四个参数为null
    // 如果写成大于0的整数那么表示将在规定时间内超时
    // 如果写成等于0的整数那么表示不断调用select，执行后立马返回，然后继续
    // 如果写成null，那么表示select会阻塞一直到监听发生变化
    if (socket_select($read, $write, $exp, null) > 0) {
        // 判断listen_socket有没有发生变化，如果有就是有客户端发生连接操作了
        if (in_array($listen_socket, $read)) {
            // 将客户端socket加入到client数组中
            $client_socket = socket_accept($listen_socket);
            $client[]      = $client_socket;
            // 然后将listen_socket从read中去除掉
            $key = array_search($listen_socket, $read);
            unset($read[$key]);
        }
        // 查看去除listen_socket中是否还有client_socket
        if (count($read) > 0) {
            $msg = 'hello world';
            foreach ($read as $socket_item) {
                // 从可读取的fd中读取出来数据内容，然后发送给其他客户端
                $content = socket_read($socket_item, 2048);
                // 循环client数组，将内容发送给其余所有客户端
                foreach ($client as $client_socket) {
                    // 因为client数组中包含了 listen_socket 以及当前发送者自己socket，所以需要排除二者
                    if ($client_socket != $listen_socket && $client_socket != $socket_item) {
                        socket_write($client_socket, $content, strlen($content));
                    }
                }
            }
        }
    }
    // 当select没有监听到可操作fd的时候，直接continue进入下一次循环
    else {
        continue;
    }

}

```

将文件保存为server.php，然后执行`php server.php`运行服务，同时再打开三个终端，执行`telnet 127.0.0.1 9999`，然后在任何一个telnet终端中输入"I am DOG!"，再看其他两个telnet窗口，是不是感觉很屌？
不完全截图图下：

![][0] 
还没意识到问题吗？如果我们看到有三个telnet客户端连接服务器并且可以彼此之间发送消息，但是我们只用了一个进程就可以服务三个客户端，如果你愿意，可以开更多的telnet，但是服务器只需要一个进程就可以搞定，这就是IO多路复用diao的地方！
最后，我们重点解析一些socket_select函数，我们看下这个函数的原型：

```php
int socket_select ( array &$read , array &$write , array &$except , int $tv_sec [, int $tv_usec = 0 ] )
```

值得注意的是$read，$write，$except三个参数前面都有一个&，也就是说这三个参数是引用类型的，是可以被改写内容的．在上面代码案例中，服务器代码第一次执行的时候，我们要把需要监听的所有fd全部放到了read数组中，然而在当系统经历了select后，这个数组的内容就会发生改变，由原来的全部read fds变成了只包含可读的read fds，这也就是为什么声明了一个client数组，然后又声明了一个read数组，然后read = client．如果我们直接将client当作socket_select的参数，那么client数组内容就被修改．假如有5个用户保存在client数组中，只有1个可读，在经过socket_select后client中就只剩下那个可读的fd了，其余4个客户端将会丢失，此时客户端的表现就是连接莫名其妙发生丢失了．

[原文地址：[https://blog.ti-node.com/blog...][1]]

[1]: https://blog.ti-node.com/blog/6389426571769282560
[2]: https://blog.ti-node.com/blog/6382424397004668928
[3]: https://blog.ti-node.com/blog/6389362802519179264
[4]: https://blog.ti-node.com/blog/6389426571769282560
[0]: ./img/1460000016237663.png