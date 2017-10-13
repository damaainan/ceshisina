## [PHP+Swoole的闭包写法][0]

 2016年10月25日  [韩 天峰][1]  

JS程序员总是嘲笑PHP没有闭包，今天抽空写一篇文章来专门介绍一下PHP的闭包。从5.3版本开始PHP就增加了匿名函数支持，经过数个版本迭代到现在的PHP5.6、PHP7，PHP语言的闭包已经非常完善了。再结合Swoole提供的事件驱动支持，PHP的闭包功能非常强大而且很优雅。

## 匿名函数

匿名函数是闭包的核心，匿名函数在PHP里实际上是一个Closure类的**对象**（请注意是对象）。与普通的面向对象编程方式不同，匿名函数的代码是直接写在调用处的，不需要额外写一个类，编写方法的代码。这样的好处就是更直接。下面的示例是设置一个定时器，每2秒输出hello world。

### 传统写法

```php
    function timer () {
        echo "hello world";
    }
    Swoole\Timer::tick(2000, 'timer');
```

### 闭包写法

```php
    Swoole\Timer::tick(2000, function () {
        echo "hello world";
    });
```

非闭包的传统写法，先要声明一个函数，再转入函数名称字符串。两段代码是分离的，不够直观。而闭包的写法把定时器的声明和定时器要执行的代码写在了一起，逻辑非常清晰直观。使用闭包语法可以很方便编写回调函数。在事件驱动编程、排序、array_walk等需要用户传入一段执行代码的场景中，闭包的写法非常优雅。

闭包更强大的地方在于它可以直接在调用处引入外部变量。PHP中实现的方法就是use关键词。

## Use语法

如果刚才的定时器需要传入一个变量，传统的写法只能通过全局变量来实现。与JS不同，PHP的变量引入是显式的，如果要引用外部变量必须使用use来声明。而JS是隐式的，匿名函数内部可以随意操作外部变量，无需声明。这样好处是少写了一点代码，缺点是存在风险和混乱。

### 传统写法

```php
    $str = "hello world";
    function timer () {
        global $str;
        echo $str;
    }
    Swoole\Timer::tick(2000, 'timer');
```

### 闭包写法

```php
    $str = "hello world";
    Swoole\Timer::tick(2000, function () use ($str) {
        echo $str;
    });
```

闭包写法使用use直接引入了当前的$str变量，而不需要使用global全局变量。另外如果是在swoole的事件驱动编程模式，使用global就无法实现异步并发了，因为global全局变量只有1个，如果同时有多个客户端请求，每个请求要查询数据库，输出不同的内容，传统的编程方法就不太容易实现，需要使用全局变量数组，以客户端的ID为KEY保存各自的数据。

### 传统写法

```php
    $requestArray = array();
    $dbResultArray = array();
    
    function my_request($request, $response) {
        global $dbResultArray, $requestArray;
        $queryId = $db->query($sql, 'get_result');
        $requestArray[$request->fd] = array($request, $response);
        $dbResultArray[$queryId] = $request->fd;
    }
    
    function get_result($queryId, $queryResult) {
        global $dbResultArray, $requestArray;
        list($request, $response) = $requestArray[$dbResultArray[$queryId]];
        $response->end($queryResult);
    }
    
    $server->on('request', 'my_request');
```

### 闭包写法

```php
    $server->on('request', function ($request, $response) {
        $queryId = $db->query($sql, function ($queryId, $queryResult) use ($request, $response) {
            $response->end($queryResult);
        });
    });
```

传统的写法非常复杂，需要反复多次从全局数组保存/提取数据。而闭包的写法非常简洁优雅，只用了几行代码就实现了同样的功能。闭包写法非常适合用来编写异步非阻塞回调模式的服务器程序。目前热门的编程语言中只有PHP和JS具备这种能力。

## 闭包更多特性

在类的方法中使用匿名函数，5.4以上的版本无需使用use引入$this，直接可以在匿名函数中使用$this来调用当前对象的方法。在swoole编程中，可以利用此特性减少$serv对象的use引入传递。

```
    class Server extends Swoole\Server {
        function onReceive($serv, $fd, $reactorId, $data) {
            $db->query($sql, function ($queryId, $queryResult) use ($fd) {
                $this->send($fd, $queryResult);
            }
        }
    }
```

另外如果希望在闭包函数中修改外部变量，可以在use时为变量增加&引用符号即可。注意对象类型不需要加&，因为在PHP中对象默认就是传引用而非传值。

[0]: http://rango.swoole.com/archives/547
[1]: http://rango.swoole.com/archives/author/matyhtf