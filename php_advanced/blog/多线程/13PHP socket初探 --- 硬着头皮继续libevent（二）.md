## PHP socket初探 --- 硬着头皮继续libevent（二）

来源：[https://segmentfault.com/a/1190000016254243](https://segmentfault.com/a/1190000016254243)

[原文地址：[https://blog.ti-node.com/blog...][9]]

实际上php.net上是有event扩展的使用说明手册，但是呢，对于初学者来说却并没有什么卵用，因为没有太多的强有力使用案例代码，也没有给力的User Contributed Notes，所以可能造成的结果就是：根本就看不懂。

这就是event文档，[点击这里][10]，你们可以感受一下。从文档上看，event扩展一共实现了如下图几个基础类，其中最常用重要的就是Event和EventBase以及EventConfig三个类了，所以，先围绕这三位开展一下工作。

![][0]

考虑到你们、我、还有正在看这个文章的其他未知物种，大多数可能并不是搞C语言的老兵油子，所以我得用一些可能并不恰当的案例和比喻来尝试引入这些概念。

libevent中有五个字母是event，实际上就是说“event才是王道”。

Event类就是产生各种不同类型事件的产出器，比如定时器事件、读写事件等等，为了提升民族荣誉感，我们将这些各种事件比作各种战斗机：比如歼10、歼15和歼20。

![][1]

EventBase类就相对容易介入了，这玩意显然就是一个航空母舰了，为了提升民族荣誉感，我们就把EventBase类当作是辽宁舰。各种Event都必须依靠EventBase才能混口饭吃，这和战斗机有辽宁舰才有底气飞的更高更远是一个道理。一定是先有航母（EventBase），其次是战斗机（Event）挂在航母（EventBase）上。

![][2]

EventConfig则是一个配置类，实例化后的对象作为参数可以传递给EventBase类，这样在初始化EventBase类的时候会根据这个配置初始化出不同的EventBase实例。类比的话，这个类则有点儿类似于辽宁舰的舰岛，可以配置指挥整个辽宁舰。航空母舰的发展趋势是不需要舰岛的，同样，在实例化EventBase类时候同样也可以不传入EventConfig对象，直接进行实例化也是没有问题的。

下面我们从开始写一个php定时器来步入到代码的节奏中。定时器是大家常用的一个工具，一般phper一说定时器，脑海中第一个想起的绝逼是Linux中的crontab。难道phper们离开了crontab真的就没法混了吗？是的，真的好羞耻，现实告诉我们就是这样的，他们离开了crontab真的就没法混了。那么，是时候通过纯php来搞一波儿定时器实现了！

注意是真的纯php，连Event扩展都不用的那种。

```php
<?php
// 给当前php进程安装一个alarm信号处理器
// 当进程收到alarm时钟信号后会作出动作
pcntl_signal(SIGALRM, function () {
    echo "tick." . PHP_EOL;
});
// 定义一个时钟间隔时间，1秒钟吧
$tick = 1;
while (true) {
    // 当过了tick时间后，向进程发送一个alarm信号
    pcntl_alarm($tick);
    // 分发信号，呼唤起安装好的各种信号处理器
    pcntl_signal_dispatch();
    // 睡个1秒钟，继续
    sleep($tick);
}

```

代码保存成timer.php，然后php timer.php运行下，如果不出问题应该能跑起来。但是吧，这个代码有一坨问题。

* 首先是性能一般（ 但是，比使用declare(ticks=1)还是要好不少的 ）
* 其次是代码量确实短小，短小的都让人怀疑：这特么玩意能用？
* 最后是即便我硬着头皮用，但这玩意只能精确到秒级，逗我？


所以，为了解决以上问题，是时候操作一波儿Event扩展了！

```php
<?php
// 初始化一个EventConfig（舰岛），虽然是个仅用于演示的空配置
$eventConfig = new EventConfig();
// 根据EventConfig初始化一个EventBase（辽宁舰，根据舰岛配置下辽宁舰）
$eventBase = new EventBase($eventConfig);
// 初始化一个定时器event（歼15，然后放到辽宁舰机库中）
$timer = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, function () {
    echo microtime(true) . " : 歼15，滑跃，起飞！" . PHP_EOL;
});
// tick间隔为0.05秒钟，我们还可以改成0.5秒钟甚至0.001秒，也就是毫秒级定时器
$tick = 0.05;
// 将定时器event添加（将歼15拖到甲板加上弹射器）
$timer->add($tick);
// eventBase进入loop状态（辽宁舰！走你！）
$eventBase->loop();

```

将代码保存为tick.php，然后php tick.php执行一下，如下图所示：

![][3]

这种定时器是持久的定时器（每隔X时间一定会执行一次），如果想要一次性的定时器（隔X时间后就会执行一次，执行过后再也不执行了），那么将上述代码中的“Event::TIMEOUT | Event::PERSIST”修改为“Event::TIMEOUT”即可。

如果你有一些自定义用户数据传递给回调函数，可以利用new Event()的第五个参数，这五个参数可以给回调函数用，如下所示：

```php
<?php
$timer = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, function () use (&$custom) {
    //echo microtime( true )." : 歼15，滑跃，起飞！".PHP_EOL;
    print_r($custom);
}, $custom = array(
    'name' => 'woshishui',
));

```

需要重点说明的是new Event()这行代码了，我把原型贴过来给大家看下：

```php
public Event::__construct ( EventBase $base , mixed $fd , int $what , callable $cb [, mixed $arg = NULL ] )
```

* 第一个参数是一个eventBase对象即可
* 第二个参数是文件描述符，可以是一个监听socket、一个连接socket、一个fopen打开的文件或者stream流等。如果是时钟时间，则传入-1。如果是其他信号事件，用相应的信号常量即可，比如SIGHUP、SIGTERM等等
* 第三个参数表示事件类型，依次是Event::READ、Event::WRITE、Event::SIGNAL、Event::TIMEOUT。其中，加上Event::PERSIST则表示是持久发生，而不是只发生一次就再也没反应了。比如Event::READ | Event::PERSIST就表示某个文件描述第一次可读的时候发生一次，后面如果又可读就绪了那么还会继续发生一次。
* 第四个参数就熟悉的很了，就是事件回调了，意思就是当某个事件发生后那么应该具体做什么相应
* 第五个参数是自定义数据，这个数据会传递给第四个参数的回调函数，回调函数中可以用这个数据。


通过以上的案例代码可以总结一下日常流程：

* 创建EventConfig（非必需）
* 创建EventBase
* 创建Event
* 将Event挂起，也就是执行了Event对象的add方法，不执行add方法那么这个event对象就无法挂起，也就不会执行
* 将EventBase执行进入循环中，也就是loop方法


捋清楚了定时器代码，我们尝试来解决一个信号的问题。比如我们的进程是常驻内存的daemon，再接收到某个信号后就会作出相应的动作，比如收到term信号后进程就会退出、收到usr1信号就会执行reload等等。

```php
<?php
// 依然是照例行事，尽管暂时没什么实际意义上的配置
$eventConfig = new EventConfig();
// 初始化eventBase
$eventBase = new EventBase($eventConfig);
// 初始化event
$event = new Event($eventBase, SIGTERM, Event::SIGNAL, function () {
    echo "signal term." . PHP_EOL;
});
// 挂起event对象
$event->add();
// 进入循环
echo "进入循环" . PHP_EOL;
$eventBase->loop();

```

将代码保存成tick.php，然后执行php tick.php，代码已经进入循环了，然后我们打开另外一个终端，输入ps aux|grep tick查看一个php进程的pid进程号，对这个进程发送term信号，如下图所示：

![][4] 

![][5]

奇怪啊，从第一张图看到确实收到term信号了，但是很奇怪为什么这个php进程退出了呢？是因为没有添加Event::PERSIST，修改如下代码如下：

```php
<?php
$event = new Event( $eventBase, SIGTERM, Event::SIGNAL | Event::PERSIST, function(){
    echo "signal term.".PHP_EOL;
} );
```

有些心眼多鸡贼的，IO多路复用的方法一共有三个select、poll和epoll（Mac下叫做kqueue），那么我们当前的event扩展用的是哪个方法呢？那么，再表演一波儿：

```php
<?php
// 查看当前系统平台支持的IO多路复用的方法都有哪些？
$method = Event::getSupportedMethods();
print_r($method);
// 查看当前用的方法是哪一个？
$eventBase = new EventBase();
echo "当前event的方法是：" . $eventBase->getMethod() . PHP_EOL;
// 跑了许久龙套的config这次也得真的露露手脚了
$eventConfig = new EventConfig;
// 避免使用方法kqueue
$eventConfig->avoidMethod('kqueue');
// 利用config初始化event base
$eventBase = new EventBase($eventConfig);
echo "当前event的方法是：" . $eventBase->getMethod() . PHP_EOL;

```

将代码保存了，然后执行一下，可以看到结果如下图所示：

![][6]

那么，还有一些更鸡贼的人继续发问，前面提到的边缘触发和水平触发，如何确认呢？既然都用上epoll或者kqueue了，就一定要用边缘触发。

```php
<?php
$base = new EventBase();
echo "特性：" . PHP_EOL;
$features = $base->getFeatures();
// 看不到这个判断条件的，请反思自己“位运算”相关欠缺
if ($features & EventConfig::FEATURE_ET) {
    echo "边缘触发" . PHP_EOL;
}
if ($features & EventConfig::FEATURE_O1) {
    echo "O1添加删除事件" . PHP_EOL;
}
if ($features & EventConfig::FEATURE_FDS) {
    echo "任意文件描述符，不光socket" . PHP_EOL;
}

```

运行结果如下图所示：

![][7]
## 小小装个逼总结一下，今儿这些个内容就是讲述event的基础三大类，下个篇章依然是围绕这三个家伙和IO操作结合到一起。

[原文地址：[https://blog.ti-node.com/blog...][9]]

![][8]

[原文地址：[https://blog.ti-node.com/blog...][9]]

[9]: https://blog.ti-node.com/blog/6396317917192912897
[10]: http://php.net/manual/en/book.event.php
[11]: https://blog.ti-node.com/blog/6396317917192912897
[12]: https://blog.ti-node.com/blog/6396317917192912897
[0]: ./img/1460000016254246.png
[1]: ./img/1460000016254247.png
[2]: ./img/1460000016254248.png
[3]: ./img/1460000016254249.png
[4]: ./img/1460000016254250.png
[5]: ./img/1460000016254251.png
[6]: ./img/1460000016254252.png
[7]: ./img/1460000016254253.png
[8]: ./img/bVbgmDe.png