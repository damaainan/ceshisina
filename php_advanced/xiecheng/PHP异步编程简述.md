# PHP异步编程简述

 时间 2018-01-29 16:52:13  

原文[http://blog.p2hp.com/archives/5055][1]


## 概述

异步编程，我们从字面上理解，可以理解为代码非同步执行的。异步编程可以归结为 [四种模式][4] ：回调、事件监听、发布/订阅、promise模式。我们最熟悉的两种模式是回调和事件监听，举两个最简单的javascript例子，一个ajax，一个点击事件的绑定： 
```js
$.getJSON("uri", params, function(result) {
    do_something_with_data(result);
});


$("#id").click(function(){
    do_something_when_user_click_id();
});
```

以上两个示例有一个共同的特点，就是把函数当做参数传递给另一个函数。被传递的函数可以被称作为闭包，闭包的执行取决于父函数何时调用它。

## 优势与劣势 

### 异步编程具有以下优势： 

* 解耦，你可以通过事件绑定，将复杂的业务逻辑分拆为多个事件处理逻辑
* 并发，结合非阻塞的IO，可以在单个进程（或线程）内实现对IO的并发访问；例如请求多个URL，读写多个文件等
* 效率，在没有事件机制的场景中，我们往往需要使用轮询的方式判断一个事件是否产生

### 异步编程的劣势： 

异步编程的劣势其实很明显——回调嵌套。相信一部分人在写ajax的时候遇到过这样的场景：
```js
$.getJSON("uri", params, function(result_1) {
    $.getJSON("uri", result_1, function(result_2) {
        $.getJSON("uri", result_2, function(result_3) {
            do_something_with_data(result_3);
        });
    });;
});
```

这样的写法往往是因为数据的依赖问题，第二次ajax请求依赖于第一次请求的返回结果，第三次ajax依赖于第二次。这样就造成深层次的回调嵌套，代码的可读性急剧下降。虽然有一些框架能够通过一些模式解决这样的问题，然并卵，代码的可读性相比同步的写法依然差很多。

异步编程的另一个劣势就是编写和调试的过程更加复杂，有时候你不知道什么时候你的函数才会被调用，以及他们被调用的顺序。而我们更习惯同步串行的编程方式。

然而，我相信一旦你开始使用异步编程，你一定会喜欢上这种方式，因为他能够带给你更多的便利。

## PHP异步编程概述 

在php语言中，异步的使用并不像javascript中那么多，归其原因主要是php一般是在web环境下工作，接收请求->读取数据->生成页面，这看起来天生就是一个串行的过程；所以，在php中，异步并没有广泛使用。

在javascript中的4中异步编程模式，均可以在php中实现。
```php
array_walk($arr, function($key, $value){
    $value += 1;
});
print_r($arr);
```

回调的方式，在大多情况下，代码仍然是顺序执行的（array_walk->print_r的顺序）。回调函数的意义在于被传递者可以调用回调函数对数据进行处理，这样的好处在于提供更好的扩展性和解耦。我们可以把这个回调函数理解为一个格式化器，处理相同的数据，当我传递一个json过滤器时，返回的结果可能是一个json压缩过的字符串，当我传递的是一个xml过滤器时，返回的结果可能是一个xml字符串（有点多态的思想）。

### 事件监听（定时器，时间事件）： 
```php
$loop = React\EventLoop\Factory::create();
$loop->addPeriodicTimer(5, function () {
    $memory = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3).'K';
    echo "Current memory usage: {$formatted}\n";
});

$loop->run();
```

事件监听在PHP中用的并不多，但并不是没有，例如pcntl_signal()监听操作系统信号，以及其他IO事件的监听等等。上面的示例是一个事件事件的侦听，每隔5s中，会执行一次回调函数。

在四种异步模式中，事件监听的应用是更有意义的。然我们看一个同步的例子，下面这段代码用于向百度和google（一个不存在的网站）发起请求，同步的编写写法是先去请求百度或者google，等待请求结束后再请求另一个：
```php
$http = new HTTP();
echo $http->get('http://www.baidu.com');
echo $http->get('http://www.google.com');
```

基于事件的处理方式可以是这样的：
```php
$http = new HTTP();
$http->get('www.baidu.com');
$http->get('www.huyanping.cn');
$http->on('response', function($response){
    echo $response  . PHP_EOL;
});
$http->run();
```

异步的写法允许我们同时处理多个事务，谁先完成，就先去处理谁。一个简单的异步http客户端见： [async-http-php][5]

PHP有很多扩展和包提供了这方面的支持：

[ext-libevent][6] libevent扩展，基于libevent库，支持异步IO和时间事件 

ext-event event扩展，支持异步IO和时间事件

[ext-libev][7] libev扩展，基于libev库，支持异步IO和时间事件 

[ext-eio][8] eio扩展，基于eio库，支持磁盘异步操作 

[ext-swoole][9] swoole扩展，支持异步IO和时间，方便编写异步socket服务器，推荐使用 

[package-react][10] react包，提供了全面的异步编程方式，包括IO、时间事件、磁盘IO等等 

[package-workerman][11] workerman包，类似swoole，php编写 

### 发布/订阅： 
```php
$lookup = new nsqphp\Lookup\Nsqlookupd;
$nsq = new nsqphp\nsqphp($lookup);
$nsq->subscribe('mytopic', 'somechannel', function($msg) {
    echo $msg->getId() . "\n";
})->run();
```

### promise： 

```php
function getJsonResult()
{
    return queryApi()
    ->then(
        // Transform API results to an object
        function ($jsonResultString) {
            return json_decode($jsonResultString);
        },
        // Transform API errors to an exception
        function ($jsonErrorString) {
            $object = json_decode($jsonErrorString);
            throw new  ApiErrorException($object->errorMessage);
        }
    );
}

// Here we provide no rejection handler. If the promise returned has been
// rejected, the ApiErrorException will be thrown
getJsonResult()
->done(
    // Consume transformed object
    function ($jsonResultObject) {
        // Do something with $jsonResultObject
    }
);
```

promise模式的意义在于解耦，就在刚刚我们提到的异步回调嵌套的问题，可以通过promise解决。其原理是在每一次传递回调函数的过程中，你都会拿到一个promie对象，而这个对象有一个then方法，then方法仍然可以返回一个promise对象，通过传递promise对象可以实现把多层嵌套分离出来。具体的代码需要去研究一下源码才可以，有点难懂，PHP的promise推荐阅读： [promise][12]

## 异步的实现原理 

异步的实现大多情况下少不了循环监听事件，例如我们上面看到$loop->run()，这里其实是一个死循环，监听到事件则调用相应的处理函数。如果你对pcntl熟悉，你一定知道declare(tick=1)，其实它也是一种循环，含义是每执行tick行代码，则检查一次是否有尚未处理的信号。虽然会有一个阻塞的死循环（大多数情况下，declare属于特殊情况），但我们可以对多个事件进行监听处理，同时可以在某一个事件处理的过程中停止循环，这样就可以实现并发异步的IO访问，甚至更多。

一段伪代码如下：

    $async = new Async();
    $async->on('request', function($requset){
        do_something_with($request);
    });
    
    // 这里其实就是$loop->run()的核心代码
    while(true){
        $async->hasRequest() ? $async->callRequestCallback() : null;
        sleep(1);
    }


整片文章其实并不够详细，充其量算是一篇介绍性的文章，算是我在异步编程方面的一次总结。异步编程的学习并不像学习一门语言或者设计模式那样简单，它要求我们改变传统的编程方式。而异步IO对于学习者要求也略高，首先你必须熟悉同步的IO操作，甚至你需要了解一些协议解析的内容。

希望上面的内容对于初学者有一些帮助。文中若有错误的地方，还望指正。

[1]: http://blog.p2hp.com/archives/5055
[4]: http://www.ruanyifeng.com/blog/2012/12/asynchronous%EF%BC%BFjavascript.html
[5]: https://github.com/huyanping/async-http-php
[6]: http://php.net/manual/zh/book.libevent.php
[7]: http://php.net/manual/zh/book.ev.php
[8]: http://php.net/manual/zh/book.eio.php
[9]: http://www.swoole.com/
[10]: https://github.com/reactphp/react
[11]: http://www.workerman.net/
[12]: https://github.com/reactphp/promise