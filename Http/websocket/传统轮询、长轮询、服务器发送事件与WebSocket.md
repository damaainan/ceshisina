## 传统轮询、长轮询、服务器发送事件与WebSocket

<font face=微软雅黑>

* 发布时间：2017-03-09
* 分类：[编程心得][0]


- - -

构造网络应用的过程中，我们经常需要与服务器进行持续的通讯以保持双方信息的同步。通常这种持久通讯在不刷新页面的情况下进行，消耗一定的内在资源常驻后台，并且对于用户不可见。比如：一个问答系统，当用户发表问题后，如果有其它用户回答，则提问者应该在不刷新页面的情况下，及时收到这个回答的内容。对于这样的需求，我们一般常用的解决方案是`短轮询`、`长轮询`、`服务器推送`、`websocket`。

## 短轮询

短轮询是一种常见的使用方法，这种方法实现起来比较简单，就是客户端每隔一段时间去服务器请求一次。如果有新的数据返回，就刷新内容。而在服务端收到请求后，不管是否有新数据，都直接响应 `http` 请求。这种方法，对于服务器端，不需要有什么特别的要求。对于客户端，一般采用 `setInterval` 或 `setTimeout` 实现。

**服务端代码**

```php
    <?php
    header('Content-type: application/json; charset=UTF-8');
    // 从数据库里查看，是不是有新回答（为了提高效率，可以采用 nosql 数据库 ），这里不做具体的实现
    $data = array(); 
    
    // 如果没有新回答，则返回空
    if( !is_array($data) || empty($data) ) exit(json_encode( array('errcode'=>1, 'errmsg'=>'error') )); 
    
    // 如果有新的回答，则返回数据
    exit(json_encode( array('errcode'=>0, 'errmsg'=>'success', 'data'=>$data) ));
    ?>
```

**客户端代码**

```js
    setInterval(function() {
        $.post("http://www.study.com/index.php", function(data, status) 
        {
            console.log(data);
            if( data.errcode != '1' )
            {
                // 刷新页面内容
            }
        }, 'json');
    }, 10000);
```

这个程序会每隔10秒向服务器请求一次数据，如果请求后有新的内容，则去刷新页面。这个实现方法通常可以满足简单的需求，然而同时也存在着很大的缺陷：网络情况不稳定的情况下，服务器从接收请求、发送数据到客户端的总时间有可能会超过10秒，而客户端是每隔10秒请求一次的，所以，这样会导致接收数据的顺序和发送顺序不一致，第二次请求的结果可能会比每一次请求的结果还要先拿到。针对这种情况，我们可以采用 `setTimeout` 的轮询方式：

```js
    function poll() 
    {
        setTimeout(function() 
        {
            $.get("http://www.study.com/index.php", function(data, status) 
            {
                console.log(data);
                
                // 发起下一次请求
                poll();
            });
        }, 10000);
    }
    poll();
```

程序首先会设置 10 秒后发起请求，当数据返回后，再隔10秒发起第二次请求，以此类推。这样的话虽然无法保证两次请求之间的时间间隔为固定值，但是可以保证到达数据的顺序。

## 长轮询

`短轮询`方式存在一个严重缺陷：程序在每次请求时都会新建一个`HTTP`请求，然而并不是每次都能返回所需的新数据。当同时发起的请求达到一定数目时，会对服务器造成较大负担。这时我们可以采用`长轮询`方式解决这个问题。

`长轮询`和`短轮询`原理是一样的，只不过在服务器端收到请求后，不是马上响应；而是停留一段时间，直到有新的数据或请求超时；

**服务端代码**

```php
    <?php
    // 关闭脚本最大执行时间 ，这样可以保证代码一直运行
    set_time_limit(0);
    
    header('Content-type: application/json; charset=UTF-8');
    
    $data = array();
    
    while( ! $data )
    {
        // 这里从数据库查询，是不是有新的回答（ 注意：千万不能直接不停的去操作 mysql 
        // ，如果不停的操作查询，数据库会疯了。可以采用 nosql 或 缓存，或 redis发布订阅等等 ）
        $data = array();
    
        // 每次查询数据库的时间间隔是10秒
        if( ! $data ) sleep(10);
    }
    
    // 如果有新的回答，则返回数据
    exit(json_encode( array('errcode'=>0, 'errmsg'=>'success', 'data'=>$data) ));
    ?>
```

**客户端代码**

```js
    function longPoll () 
    {
        var _timestamp;
        $.get("http://www.study.com/index.php")
        .done(function(res) {
            try {
                console.log(res);
            } catch (e) {}
        })
        .always(function() {
            setTimeout(function() 
            {
                longPoll();
            }, 10000);
        });
    }   
    longPoll();
```

由以上两个程序可以看出，`长轮询`是在服务器端的停留，而短轮询是在浏览器端的停留。`长轮询`可以减少请求次数，有效地解决短轮询带来的带宽浪费，但是每次连接的保持是以消耗服务器资源为代价的。所以，不管是长轮询还是`短轮询`，都不太适用于客户端数量太多的情况，因为每个服务器所能承载的TCP连接数是有上限的，这种轮询很容易把连接数顶满；

## 服务器发送事件

`服务器发送事件`（以下简称`SSE`）是HTML 5规范的一个组成部分，可以实现服务器到客户端的单向数据通信。通过`SSE`，客户端可以自动获取数据更新，而不用重复发送HTTP请求。一旦连接建立，“事件”便会自动被推送到客户端。服务器端`SSE`通过“事件流(Event Stream)”的格式产生并推送事件。事件流对应的`MIME类型`为“text/event-stream”，包含四个字段：`event`、`data`、`id`和`retry`。`even`t表示事件类型，`data`表示消息内容，id用于设置客户端`EventSource`对象的“last event ID string”内部属性，`retry`指定了重新连接的时间。

**服务端代码**

```php
    <?php
    header("Content-Type: text/event-stream");
    header("Cache-Control: no-cache");
    $time = date("r");
    echo "event: ping\n";
    echo "retry: 3000\n"; // 表示该行用来声明浏览器在连接断开之后进行再次连接之前的等待时间
    echo "data: The server time is: {$time}\n\n";
```

**客户端代码**

```js
    var eventSource = new EventSource("http://www.study.com/index.php");
    eventSource.addEventListener("ping", function(e) 
    {
        console.log(e)
    }, false);
```

`SSE`相较于`轮询`具有较好的实时性，使用方法也非常简便。然而`SSE`只支持服务器到客户端单向的事件推送，而且所有版本的IE（包括到目前为止的Microsoft Edge）都不支持SSE。如果需要强行支持IE和部分移动端浏览器，可以尝试`EventSource Polyfill`（本质上仍然是轮询）。`SSE`的浏览器支持情况如下图所示：

![server-sent-event.JPG][6]

## WebSocket

`WebSocket`同样是HTML 5规范的组成部分之一，现标准版本为RFC 6455。`WebSocket`相较于上述几种连接方式，实现原理较为复杂，用一句话概括就是：客户端向`WebSocket`服务器通知（`notify`）一个带有所有接收者ID（recipients IDs）的事件（`event`），服务器接收后立即通知所有活跃的（active）客户端，只有ID在接收者ID序列中的客户端才会处理这个事件。由于`WebSocket`本身是基于`TCP`协议的，所以在服务器端我们可以采用构建`TCP Socket`服务器的方式来构建`WebSocket`服务器。

服务端代码

```php
    <?php
    $serv = new Swoole\Websocket\Server("127.0.0.1", 9501);
    
    $serv->on('Open', function($server, $req) 
    {
        echo "connection open: ".$req->fd;
    });
    
    $serv->on('Message', function($server, $frame) 
    {
        echo "message: ".$frame->data;
        $server->push($frame->fd, json_encode(["hello", "world"]));
    });
    
    $serv->on('Close', function($server, $fd) 
    {
        echo "connection close: ".$fd;
    });
    
    $serv->start();
```

 这段代码是采用了 `swoole`，请在运行前确保安装了 `swoole`，并在 `cli` 环境下运行

**服务端代码**

```js
    var url='ws://127.0.0.1:9501';
    socket=new WebSocket(url);
    
    socket.onopen=function()
    {
        socket.send('type=add&ming=hello');
    }
    
    socket.onmessage=function(msg)
    {
        console.log( msg )
    }
    
    socket.onclose= function()
    {
        console.log("退出了")
    }
```

`WebSocket`同样具有实时性，每次通讯无需重发请求头部，节省带宽，而且它的浏览器支持非常好（详见下图）。

![server-sent-event.JPG][7]

## 总结

四种通信方式的优缺点


- | 短轮询 | 长轮询 | 服务器发送事件 | `WebSocket`
- |-|-|-|-
浏览器支持 |   几乎所有现代浏览器 |   几乎所有现代浏览器 |   Firefox  6+ Chrome 6+ Safari 5+ Opera 10.1+  |  IE 10+ Edge Firefox 4+ Chrome 4+ Safari 5+ Opera 11.5+
服务器负载 |   较少的CPU资源，较多的内存资源和带宽资源 |   与短轮询相似，但是占用带宽较少 | 与长轮询相似，除非每次发送请求后服务器不需要断开连接 |  无需循环等待（长轮询），CPU和内存资源不以客户端数量衡量，而是以客户端事件数衡量。四种方式里性能最佳。
客户端负载 |   占用较多的内存资源与请求数 |   与传统轮询相似 | 浏览器中原生实现，占用资源很小 | 同服务器发送事件
延迟 |  非实时，延迟取决于请求间隔 |   同短轮询 |    非实时，默认3秒延迟，延迟可自定义 |   实时
实现复杂度 |   非常简单 |    需要服务器配合，客户端实现非常简单 |   需要服务器配合，而客户端实现甚至比前两种更简单 | 需要Socket程序实现和额外端口，客户端实现简单

</font>

[0]: http://www.yduba.com/biancheng/
[1]: http://www.yduba.com/
[6]: ../img/1489051012825193.jpg
[7]: ../img/1489051102578942.jpg