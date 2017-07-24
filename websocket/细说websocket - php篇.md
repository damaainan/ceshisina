## [细说websocket - php篇][0]

2013-12-25 11:24 by Barret Lee, 31465 阅读, 5 评论, [收藏][1], [编辑][2]

下面我画了一个图演示 client 和 server 之间建立 websocket 连接时握手部分，这个部分在 node 中可以十分轻松的完成，因为 node 提供的 net 模块已经对 socket 套接字做了封装处理，开发者使用的时候只需要考虑数据的交互而不用处理连接的建立。而 php 没有，从 socket 的连接、建立、绑定、监听等，这些都需要我们自己去操作，所以有必要拿出来再说一说。

 

        +--------+    1.发送Sec-WebSocket-Key        +---------+
        |        | --------------------------------> |        |
        |        |    2.加密返回Sec-WebSocket-Accept  |        |
        | client | <-------------------------------- | server |
        |        |    3.本地校验                      |        |
        |        | --------------------------------> |        |
        +--------+                                   +--------+

看了我写的[上一篇文章][3]的同学应该是对上图有了比较全面的理解。① 和 ② 实际上就是一个 HTTP 的请求和响应，只不过我们在处理的过程中我们拿到的是没有经过解析的字符串。如：

    GET /chat HTTP/1.1
    Host: server.example.com
    Origin: http://example.com

我们往常看到的请求是这个样子，当这东西到了服务器端，我们可以通过一些代码库直接拿到这些信息。

### 一、php 中处理 websocket

WebSocket 连接是由客户端主动发起的，所以一切要从客户端出发。第一步是要解析拿到客户端发过来的 Sec-WebSocket-Key 字符串。

 

    GET /chat HTTP/1.1
    Host: server.example.com
    Upgrade: websocket
    Connection: Upgrade
    Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==
    Origin: http://example.com
    Sec-WebSocket-Protocol: chat, superchat
    Sec-WebSocket-Version: 13

[前文][3]中也提到了 client 请求的格式（如上），首先 php 建立一个 socket 连接，监听端口的信息。

#### 1. socket 连接的建立

关于 socket 套接字的建立，相信很多大学修过计算机网络的人都知道了，下面是一张连接建立的过程：

![][4]
```php
    // 建立一个 socket 套接字
    $master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_bind($master, $address, $port);
    socket_listen($master);
```
相比 node，这个地方的处理实在是太麻烦了，上面几行代码并未建立连接，只不过这些代码是建立一个 socket 套接字必须要写的东西。由于处理过程稍微有复杂，所以我把各种处理写进了一个类中，方便管理和调用。

![][5]

![][6]
```php
    //demo.php
    Class WS {
        var $master;  // 连接 server 的 client
        var $sockets = array(); // 不同状态的 socket 管理
        var $handshake = false; // 判断是否握手
    
        function __construct($address, $port){
            // 建立一个 socket 套接字
            $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)   
                or die("socket_create() failed");
            socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)  
                or die("socket_option() failed");
            socket_bind($this->master, $address, $port)                    
                or die("socket_bind() failed");
            socket_listen($this->master, 2)                               
                or die("socket_listen() failed");
    
            $this->sockets[] = $this->master;
    
            // debug
            echo("Master socket  : ".$this->master."\n");
    
            while(true) {
                //自动选择来消息的 socket 如果是握手 自动选择主机
                $write = NULL;
                $except = NULL;
                socket_select($this->sockets, $write, $except, NULL);
    
                foreach ($this->sockets as $socket) {
                    //连接主机的 client 
                    if ($socket == $this->master){
                        $client = socket_accept($this->master);
                        if ($client < 0) {
                            // debug
                            echo "socket_accept() failed";
                            continue;
                        } else {
                            //connect($client);
                            array_push($this->sockets, $client);
                            echo "connect client\n";
                        }
                    } else {
                        $bytes = @socket_recv($socket,$buffer,2048,0);
                        if($bytes == 0) return;
                        if (!$this->handshake) {
                            // 如果没有握手，先握手回应
                            //doHandShake($socket, $buffer);
                            echo "shakeHands\n";
                        } else {
                            // 如果已经握手，直接接受数据，并处理
                            $buffer = decode($buffer);
                            //process($socket, $buffer); 
                            echo "send file\n";
                        }
                    }
                }
            }
        }
    }
```
 demo.php 握手连接测试代码

上面这段代码是经过我调试了的，没太大的问题，如果想测试的话，可以在 cmd 命令行中键入 php /path/to/demo.php;当然，上面只是一个类，如果要测试的话，还得新建一个实例。

    $ws = new WS('localhost', 4000);

客户端代码可以稍微简单点：

 
```js
    var ws = new WebSocket("ws://localhost:4000");
    ws.onopen = function(){
        console.log("握手成功");
    };
    ws.onerror = function(){
        console.log("error");
    };
```
运行服务器代码，当客户端连接的时候，我们可以看到：

![][7]

通过上面的代码可以清晰的看到整个交流的过程。首先是建立连接，node 中这一步已经封装到了 net 和 http 模块，然后判断是否握手，如果没有的话，就 shakeHands。这里的握手我直接就 echo 了一个单词，表示进行了这个东西，[前文][3]我们提到过握手算法，这里就直接写了。

#### 2. 提取 Sec-WebSocket-Key 信息

 
```php
    function getKey($req) {
        $key = null;
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)) { 
            $key = $match[1]; 
        }
        return $key;
    }
```
这里比较简单，直接正则匹配，websocket 信息头一定包含 Sec-WebSocket-Key，所以我们匹配起来也比较快捷~

#### 3. 加密 Sec-WebSocket-Key

 
```php
    function encry($req){
        $key = $this->getKey($req);
        $mask = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
    
        return base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    }
```
将 SHA-1 加密后的字符串再进行一次 base64 加密。如果加密算法错误，客户端在进行校检的时候会直接报错：

![][8]

#### 4. 应答 Sec-WebSocket-Accept

 
```php
    function dohandshake($socket, $req){
        // 获取加密key
        $acceptKey = $this->encry($req);
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
                   "\r\n";
    
        // 写入socket
        socket_write(socket,$upgrade.chr(0), strlen($upgrade.chr(0)));
        // 标记握手已经成功，下次接受数据采用数据帧格式
        $this->handshake = true;
    }
```
这里千万要注意，每一个请求和相应的格式，最后有一个空行，也就是 \r\n，开始测试的时候把这东西给弄丢了，纠结了半天。

![][9]

当客户端成功校检key后，会触发 onopen 函数：

![][10]

#### 5. 数据帧处理

 
```php
    // 解析数据帧
    function decode($buffer)  {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;
    
        if ($len === 126)  {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127)  {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else  {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }
```
这里涉及的编码问题在前文中已经提到过了，这里就不赘述，php 对字符处理的函数太多了，也记得不是特别清楚，这里就没有详细的介绍解码程序，直接把客户端发送的数据原样返回，可以算是一个聊天室的模式吧。

 
```php
    // 返回帧信息处理
    function frame($s) {
        $a = str_split($s, 125);
        if (count($a) == 1) {
            return "\x81" . chr(strlen($a[0])) . $a[0];
        }
        $ns = "";
        foreach ($a as $o) {
            $ns .= "\x81" . chr(strlen($o)) . $o;
        }
        return $ns;
    }
    
    // 返回数据
    function send($client, $msg){
        $msg = $this->frame($msg);
        socket_write($client, $msg, strlen($msg));
    }
```
客户端代码：

 
```js
    var ws = new WebSocket("ws://localhost:4000");
    ws.onopen = function(){
        console.log("握手成功");
    };
    ws.onmessage = function(e){
        console.log("message:" + e.data);
    };
    ws.onerror = function(){
        console.log("error");
    };
    ws.send("李靖");
```
在连通之后发送数据，服务器原样返回：

![][11]

### 二、注意问题

#### 1. websocket 版本问题

客户端在握手时的请求中有Sec-WebSocket-Version: 13，这样的版本标识，这个是一个升级版本，现在的浏览器都是使用的这个版本。而以前的版本在数据加密的部分更加麻烦，它会发送两个key：

 

    GET /chat HTTP/1.1
    Host: server.example.com
    Upgrade: websocket
    Connection: Upgrade
    Origin: http://example.com
    Sec-WebSocket-Protocol: chat, superchat
    Sec-WebSocket-Key1: xxxx
    Sec-WebSocket-Key2: xxxx

如果是这种版本（比较老，已经没在使用了），需要通过下面的方式获取

 
```php
    function encry($key1,$key2,$l8b){ //Get the numbers preg_match_all('/([\d]+)/', $key1, $key1_num); preg_match_all('/([\d]+)/', $key2, $key2_num);
    
        $key1_num = implode($key1_num[0]);
        $key2_num = implode($key2_num[0]);
        //Count spaces
        preg_match_all('/([ ]+)/', $key1, $key1_spc);
        preg_match_all('/([ ]+)/', $key2, $key2_spc);
    
        if($key1_spc==0|$key2_spc==0){ $this->log("Invalid key");return; }
        //Some math
        $key1_sec = pack("N",$key1_num / $key1_spc);
        $key2_sec = pack("N",$key2_num / $key2_spc);
    
        return md5($key1_sec.$key2_sec.$l8b,1);
    }
```
只能无限吐槽这种验证方式！相比 nodeJs 的 websocket 操作方式：

 
```js
    //服务器程序
    var crypto = require('crypto');
    var WS = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    require('net').createServer(function(o){
        var key;
        o.on('data',function(e){
            if(!key){
                //握手
                key = e.toString().match(/Sec-WebSocket-Key: (.+)/)[1];
                key = crypto.createHash('sha1').update(key + WS).digest('base64');
                o.write('HTTP/1.1 101 Switching Protocols\r\n');
                o.write('Upgrade: websocket\r\n');
                o.write('Connection: Upgrade\r\n');
                o.write('Sec-WebSocket-Accept: ' + key + '\r\n');
                o.write('\r\n');
            }else{
                console.log(e);
            };
        });
    }).listen(8000);
```
多么简洁，多么方便！有谁还愿意使用 php 呢。。。。

#### 2. 数据帧解析代码

本文没有给出 decodeFrame 这样数据帧解析代码，前文中给出了数据帧的格式，解析纯属体力活。

#### 3. 代码下载

对这部分感兴趣的同学，可以再去深究。提供了[参考代码下载][12]。

#### 4. 相关开源库参考

[http://socketo.me][13] Ratchet 为 php 封装的一个 WebSockets 库。 ]

Google 上搜索 [php+websoket+class][14]，也能找到不少相关的资料。

### 三、参考资料

* [http://www.php.net/manual/zh/ref.sockets.php][15] php manual
* [http://www.rfc-editor.org/rfc/rfc6455.txt][16] [[RFC6455][16]] WebSocket

[0]: http://www.cnblogs.com/hustskyking/p/websocket-with-php.html
[1]: #
[2]: https://i.cnblogs.com/EditPosts.aspx?postid=3490271
[3]: http://www.cnblogs.com/hustskyking/p/websocket-with-node.html
[4]: ./img/25124523-33fc2253152447d4a16bac3b6704d832.jpg
[7]: ./img/25124555-64ad002702334156a80bedef37fbf361.jpg
[8]: ./img/25124720-0518fd35df2c4f7a91f624e4bbf2ec6c.jpg
[9]: ./img/25124605-f1c4f57be53a43b1bcfb20392fe6c18a.jpg
[10]: ./img/25124618-53351ac2263d4ca89be4bd7eaca1968c.jpg
[11]: ./img/25124629-a1d032dbd01a4a958cae055a99c964eb.jpg
[12]: http://files.cnblogs.com/hustskyking/ws.zip
[13]: http://socketo.me
[14]: https://www.google.com.hk/search?q=php+websocket+class
[15]: http://www.php.net/manual/zh/ref.sockets.php
[16]: http://www.rfc-editor.org/rfc/rfc6455.txt