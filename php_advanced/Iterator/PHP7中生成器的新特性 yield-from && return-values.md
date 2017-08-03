# [PHP7中生成器的新特性 yield-from && return-values][0]

* [协程][1]
* [yield-from][2]
* [yield][3]
* [php][4]

[**小紫羽**][5] 3 小时前发布 



## 生成器委托

简单地翻译官方文档的描述：

> PHP7中，通过生成器委托（yield from），可以将其他生成器、可迭代的对象、数组委托给外层生成器。外层的生成器会先顺序 yield 委托出来的值，然后继续 yield 本身中定义的值。

利用 yield from 可以方便我们编写比较清晰生成器嵌套，而代码嵌套调用是编写复杂系统所必需的。  
上例子：

```php
    <?php
    function echoTimes($msg, $max) {
        for ($i = 1; $i <= $max; ++$i) {
            echo "$msg iteration $i\n";
            yield;
        }
    }
     
    function task() {
        yield from echoTimes('foo', 10); // print foo ten times
        echo "---\n";
        yield from echoTimes('bar', 5); // print bar five times
    }
    
    foreach (task() as $item) {
        ;
    }
```

以上将输出：

    foo iteration 1
    foo iteration 2
    foo iteration 3
    foo iteration 4
    foo iteration 5
    foo iteration 6
    foo iteration 7
    foo iteration 8
    foo iteration 9
    foo iteration 10
    ---
    bar iteration 1
    bar iteration 2
    bar iteration 3
    bar iteration 4
    bar iteration 5

自然，内部生成器也可以接受它的父生成器发送的信息或者异常，因为 yield from 为父子生成器建立一个双向的通道。不多说，上例子：

```php
    <?php
    function echoMsg($msg) {
        while (true) {
            $i = yield;
            if($i === null){
                break;
            }
            if(!is_numeric($i)){
                throw new Exception("Hoo! must give me a number");
            }
            echo "$msg iteration $i\n";
        }
    }
    function task2() {
        yield from echoMsg('foo');
        echo "---\n";
        yield from echoMsg('bar');
    }
    $gen = task2();
    foreach (range(1,10) as $num) {
        $gen->send($num);
    }
    $gen->send(null);
    foreach (range(1,5) as $num) {
        $gen->send($num);
    }
    //$gen->send("hello world"); //try it ,gay
```

输出和上个例子是一样的。

## 生成器返回值

如果生成器被迭代完成，或者运行到 return 关键字，是会给这个生成器返回值的。  
可以有两种方法获取这个返回值：

1. 使用 $ret = Generator::getReturn() 方法。
1. 使用 $ret = yield from Generator() 表达式。

上例子：

```php
    <?php
    function echoTimes($msg, $max) {
        for ($i = 1; $i <= $max; ++$i) {
            echo "$msg iteration $i\n";
            yield;
        }
        return "$msg the end value : $i\n";
    }
    
    function task() {
        $end = yield from echoTimes('foo', 10);
        echo $end;
        $gen = echoTimes('bar', 5);
        yield from $gen;
        echo $gen->getReturn();
    }
    
    foreach (task() as $item) {
        ;
    }
```

输出结果就不贴了，想必大家都猜到。

可以看到 yield from 和 return 结合使得 yield 的写法更像平时我们写的同步模式的代码了，毕竟，这就是 PHP 出生成器特性的原因之一呀。

## 一个非阻塞的web服务器

时间回到2015年，鸟哥博客上转载的一篇《 在PHP中使用协程实现多任务调度》。文章介绍了PHP5 的迭代生成器，协程，并实现了一个简单的非阻塞 web 服务器。（链接见文末引用）

现在我们利用 PHP7 中的这两个新特性重写这个 web 服务器，只需要 100 多行代码。

代码如下：

```php
    <?php
    
    class CoSocket
    {
        protected $masterCoSocket = null;
        public $socket;
        protected $handleCallback;
        public $streamPoolRead = [];
        public $streamPoolWrite = [];
    
        public function __construct($socket, CoSocket $master = null)
        {
            $this->socket = $socket;
            $this->masterCoSocket = $master ?? $this;
        }
    
        public function accept()
        {
            $isSelect = yield from $this->onRead();
            $acceptS = null;
            if ($isSelect && $as = stream_socket_accept($this->socket, 0)) {
                $acceptS = new CoSocket($as, $this);
            }
            return $acceptS;
        }
    
        public function read($size)
        {
            yield from $this->onRead();
            yield ($data = fread($this->socket, $size));
            return $data;
        }
    
        public function write($string)
        {
            yield from $this->onWriter();
            yield fwrite($this->socket, $string);
        }
    
        public function close()
        {
            unset($this->masterCoSocket->streamPoolRead[(int)$this->socket]);
            unset($this->masterCoSocket->streamPoolWrite[(int)$this->socket]);
            yield ($success = @fclose($this->socket));
            return $success;
        }
    
        public function onRead($timeout = null)
        {
            $this->masterCoSocket->streamPoolRead[(int)$this->socket] = $this->socket;
            $pool = $this->masterCoSocket->streamPoolRead;
            $rSocks = [];
            $wSocks = $eSocks = null;
            foreach ($pool as $item) {
                $rSocks[] = $item;
            }
            yield ($num = stream_select($rSocks, $wSocks, $eSocks, $timeout));
            return $num;
        }
    
        public function onWriter($timeout = null)
        {
            $this->masterCoSocket->streamPoolWrite[(int)$this->socket] = $this->socket;
            $pool = $this->masterCoSocket->streamPoolRead;
            $wSocks = [];
            $rSocks = $eSocks = null;
            foreach ($pool as $item) {
                $wSocks[] = $item;
            }
            yield ($num = stream_select($rSocks, $wSocks, $eSocks, $timeout));
            return $num;
        }
    
        public function onRequest()
        {
            /** @var self $socket */
            $socket = yield from $this->accept();
            if (empty($socket)) {
                return false;
            }
            $data = yield from $socket->read(8192);
            $response = call_user_func($this->handleCallback, $data);
            yield from $socket->write($response);
            return yield from $socket->close();
        }
    
        public static function start($port, callable $callback)
        {
            echo "Starting server at port $port...\n";
            $socket = @stream_socket_server("tcp://0.0.0.0:$port", $errNo, $errStr);
            if (!$socket) throw new Exception($errStr, $errNo);
            stream_set_blocking($socket, 0);
            $coSocket = new self($socket);
            $coSocket->handleCallback = $callback;
            function gen($coSocket)
            {
                /** @var self $coSocket */
                while (true) yield from $coSocket->onRequest();
            }
            foreach (gen($coSocket) as $item){};
        }
    }
    
    CoSocket::start(8000, function ($data) {
        $response = <<<RES
    HTTP/1.1 200 OK
    Content-Type: text/plain
    Content-Length: 12
    Connection: close
    
    hello world!
RES;
        return $response;
    });
```

## 参考资料

* [1] [http://www.php.net/manual/zh/...][14]
* [2] [http://www.laruence.com/2015/...][15]
* [3] [http://blog.csdn.net/u0101613...][16]

[0]: /a/1190000010479841
[1]: /t/%E5%8D%8F%E7%A8%8B/blogs
[2]: /t/yield-from/blogs
[3]: /t/yield/blogs
[4]: /t/php/blogs
[5]: /u/lizhenju
[14]: http://www.php.net/manual/zh/language.generators.syntax.php
[15]: http://www.laruence.com/2015/05/28/3038.html
[16]: http://blog.csdn.net/u010161379/article/details/51645264