## PHP: 使用FastCGI协议打造高性能网站服务 

[陈亦][1]

* 发表于 4年前
* 阅读 5112

<font face=微软雅黑>

摘要: 之前我写了一篇文章【 PHP: 深入pack/unpack 】介绍了如何在PHP中进行TCP打包和解包，以及通过分离数据层来实现可扩展和性能的提升。但是有时候性能不是衡量的唯一标准，通常需要兼顾性能和开发效率。您可能会说基于HTTP接口的开发效率不错。是的，基于HTTP协议的开发效率很高，而且它适合各种网络环境。但是由于HTTP协议需要发送大量的头部，所以导致性能不是很理想。那么有没有一种比HTTP协议性能好并且比基于TCP接口的开发效率高的解决方案呢？答案是肯定的，就是本文接下来要介绍的基于FastCGI的接口开发。 

之前我写了一篇文章【 [PHP: 深入pack/unpack][5] 】介绍了如何在PHP中进行TCP打包和解包，以及通过分离数据层来实现可扩展和性能的提升。但是有时候性能不是衡量的唯一标准，通常需要兼顾性能和开发效率。您可能会说基于HTTP接口的开发效率不错。是的，基于HTTP协议的开发效率很高，而且它适合各种网络环境。但是由于HTTP协议需要发送大量的头部，所以导致性能不是很理想。那么有没有一种比HTTP协议性能好并且比基于TCP接口的开发效率高的解决方案呢？答案是肯定的，就是本文接下来要介绍的基于FastCGI的接口开发。

## CGI是什么

`CGI` 意思为 Common Gateway Interface(公共网关接口)，它是一种规范，一种基于浏览器的输入、在Web服务器上运行的程序方法。

## FastCGI是什么

`FastCGI`是对CGI的开放的扩展，它为所有因特网应用提供高性能。

## 为什么是FastCGI

大家都知道，PHP的解释器是`php-cgi`。`php-cgi`只是个CGI程序，他自己本身只能解析请求，返回结果，不会进程管理，所以就出现了一些能够调度php-cgi进程的程序，比如说由`lighthttpd`分离出来的`spawn-fcgi`。PHP-FPM也是类似的程序，在长时间的发展后，逐渐得到了大家的认可，也越来越流行。最开始的时候 `PHP-FPM` 没有包含在PHP内核里面，要使用这个功能，需要找到与源码版本相同的 `PHP-FPM` 对内核打补丁，然后再编译。后来PHP内核集成了`PHP-FPM`之后就方便多了。

那么CGI程序的性能问题在哪呢？PHP解析器每次都会解析`php.ini`文件，初始化执行环境。标准的CGI对每个请求都会执行这些步骤，所以处理每个时间的时间会比较长。那么`FastCGI`是怎么做的呢？首先，`FastCGI`会先启一个`master`，解析配置文件，初始化执行环境，然后再启动多个`worker`。当请求过来时，`master`会传递给一个`worker`，然后立即可以接受下一个请求。这样就避免了重复的劳动，效率自然是高。而且当`worker`不够用时，`master`可以根据配置预先启动几个`worker`等着；当然空闲`worker`太多时，也会停掉一些，这样就提高了性能，也节约了资源。这就是`FastCGI`的对进程的管理。

## FastCGI协议规范

英文版: [FastCGI Specification][6] ，中文版: [http://www.itcoder.me/?p=235][7] 。本文不打算概括FastCGI的全貌，只是针对需求实现通过POST提交数据到接口。

首先以一张图来大概了解流程：

![][8]

图片来自 [ITCoder][9]

上图中的webserver称为web服务器，php称为应用。对应我们目前的需求来说，webserver就是client，php就是FastCGI管理进程。本文通篇使用web服务器和应用来描述。

请求由`FCGI_BEGIN_REQUEST`开始，`FCGI_PARAMS`表示需要传递环境变量(PHP中的`$_SERVER`数组就是通过`FCGI_PARAMS`来传递的，当然您还可以附加自定义的数据)。`FCGI_STDIN`表示一个输入的开始，比如您需要POST过去的数据。`FCGI_STDOUT`和`FCGI_STDERR` 标识应用开始响应。 `FCGI_END_REQUEST`表示一次请求的完成，由应用发送。

FastCGI是基于流的协议，并且是8字节对齐，因此不需要考虑字节序，但是要考虑填充。FastCGI的包头是固定的8字节，不同的请求有不同的包体结构。包头和包体组成一个Record(记录)。具体请参考协议规范。下面是Record结构：

```c
    typedef struct {
        unsigned char version;
        unsigned char type;
        unsigned char requestIdB1;
        unsigned char requestIdB0;
        unsigned char contentLengthB1;
        unsigned char contentLengthB0;
        unsigned char paddingLength;
        unsigned char reserved;
        unsigned char contentData[contentLength];
        unsigned char paddingData[paddingLength];
    } FCGI_Record;
```

对此 ，我们可以独立出包头，再结合各种不同的包体，即实现了Record包。但是要注意的是填充和多字节的实现。尤其是在发送名值对参数时有不同的组合方式，需要仔细处理。

先来定义常量。这些常量都是FastCGI规范定义好的。

```c
    define('FCGI_VERSION_1', 1);
    define('FCGI_BEGIN_REQUEST', 1);
    define('FCGI_RESPONDER', 1);
    define('FCGI_END_REQUEST', 3);
    define('FCGI_PARAMS', 4);
    define('FCGI_STDIN', 5);
    define('FCGI_STDOUT', 6);
    define('FCGI_STDERR', 7);
```

```php
    function getHeader($type, $requestId, $contentLength, $paddingLength, $reserved=0)
    {
        return pack("C2n2C2", FCGI_VERSION_1, $type, $requestId, $contentLength, $paddingLength, $reserved);
    }
```

填充的计算通过取模就可以了。对于用多个字符来表示单个字符，请进行移位操作，并且起始字节最高位为1。显然如果nameLen或nameValue大于`0x7f`，则需要4个字节来表示。这里有一个简单的实现：

```php
    function getNameValue($name, $value)
    {
        $nameLen  = strlen($name);
        $valueLen = strlen($value);
        $bin      = '';
    
        // 如果大于127，则需要4个字节来存储，下面的$valueLen也需要如此计算
        if ($nameLen > 0x7f)
        {
            // 将$nameLen变成4个无符号字节
            $b0 = $nameLen << 24;
            $b1 = ($nameLen << 16) >> 8;
            $b2 = ($nameLen << 8) >> 16;
            $b3 = $nameLen >> 24;
            // 将最高位置1，表示采用4个无符号字节表示
            $b3 = $b3 | 0x80;
            $bin = pack("C4", $b3, $b2, $b1, $b0);
        }
        else
        {
            $bin = pack("C", $nameLen);
        }
    
        if ($valueLen > 0x7f)
        {
            // 将$nameLen变成4个无符号字节
            $b0 = $valueLen << 24;
            $b1 = ($valueLen << 16) >> 8;
            $b2 = ($valueLen << 8) >> 16;
            $b3 = $valueLen >> 24;
            // 将最高位置1，表示采用4个无符号字节表示
            $b3 = $b3 | 0x80;
            $bin .= pack("C4", $b3, $b2, $b1, $b0);
        }
        else
        {
            $bin .= pack("C", $valueLen);
        }
    
        $bin .= pack("a{$nameLen}a{$valueLen}", $name, $value);
    
        return $bin;
    }
```

将包头和包体组成Record进行传递，比如：

```php
    $env    = array(
        'SCRIPT_FILENAME' => FCGI_SCRIPT_FILENAME,
        'REQUEST_METHOD'  => FCGI_REQUEST_METHOD,
        'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
    );
    
    foreach ($env as $key=>$value)
    {
        $body          = getNameValue($key, $value);
        $paddingLength = getPaddingLength($body);
        $header        = getHeader(FCGI_PARAMS, FCGI_REQUEST_ID, strlen($body), $paddingLength, 0);
        $record        = $header . $body . getPaddingData($paddingLength);
        socket_write($sock, $record);
    }
```

web服务器由STDIN包来结束输入。如果需要使STDIN来传递数据，则仍需要额外发送一个空包体的STDIN包来结束这次请求。之后等待应用返回，具体请参考协议规范关于type的说明。还有一些要说明的事情就是关于对应用的配置使用FCGI_PARAMS来传递，相当于nginx的fastcgi_params配置文件的内容，具体如下：

![][10]

最后web服务器解析应用返回的响应。github上有一个比较好的实现，大家可以去研究一下。有问题可以一起探讨，[PHP-FastCGI-Client][11] 。我这里大概实现了一部分，为了更接近FastCGI协议的流程，代码未作任何优化，也未作任何错误处理：

```php
    <?php
    define('FCGI_HOST', '127.0.0.1');
    define('FCGI_PORT', 9000);
    define('FCGI_SCRIPT_FILENAME', '/home/goal/fcgiclient/www/test.php');
    define('FCGI_REQUEST_METHOD', 'POST');
    define('FCGI_REQUEST_ID', 1);
    
    define('FCGI_VERSION_1', 1);
    define('FCGI_BEGIN_REQUEST', 1);
    define('FCGI_RESPONDER', 1);
    define('FCGI_END_REQUEST', 3);
    define('FCGI_PARAMS', 4);
    define('FCGI_STDIN', 5);
    define('FCGI_STDOUT', 6);
    define('FCGI_STDERR', 7);
    
    function getBeginRequestBody()
    {
        return pack("nC6", FCGI_RESPONDER, 0, 0, 0, 0, 0, 0);
    }
    
    function getHeader($type, $requestId, $contentLength, $paddingLength, $reserved=0)
    {
        return pack("C2n2C2", FCGI_VERSION_1, $type, $requestId, $contentLength, $paddingLength, $reserved);
    }
    
    function getPaddingLength($body)
    {
        $left = strlen($body) % 8;
        if ($left == 0)
        {
            return 0;
        }
    
        return (8 - $left);
    }
    
    function getPaddingData($paddingLength=0)
    {
        if ($paddingLength <= 0)
        {
            return '';
        }
        $paddingArray = array_fill(0, $paddingLength, 0);
        return call_user_func_array("pack", array_merge(array("C{$paddingLength}"), $paddingArray));
    }
    
    function getNameValue($name, $value)
    {
        $nameLen  = strlen($name);
        $valueLen = strlen($value);
        $bin      = '';
    
        // 如果大于127，则需要4个字节来存储，下面的$valueLen也需要如此计算
        if ($nameLen > 0x7f)
        {
            // 将$nameLen变成4个无符号字节
            $b0 = $nameLen << 24;
            $b1 = ($nameLen << 16) >> 8;
            $b2 = ($nameLen << 8) >> 16;
            $b3 = $nameLen >> 24;
            // 将最高位置1，表示采用4个无符号字节表示
            $b3 = $b3 | 0x80;
            $bin = pack("C4", $b3, $b2, $b1, $b0);
        }
        else
        {
            $bin = pack("C", $nameLen);
        }
    
        if ($valueLen > 0x7f)
        {
            // 将$nameLen变成4个无符号字节
            $b0 = $valueLen << 24;
            $b1 = ($valueLen << 16) >> 8;
            $b2 = ($valueLen << 8) >> 16;
            $b3 = $valueLen >> 24;
            // 将最高位置1，表示采用4个无符号字节表示
            $b3 = $b3 | 0x80;
            $bin .= pack("C4", $b3, $b2, $b1, $b0);
        }
        else
        {
            $bin .= pack("C", $valueLen);
        }
    
        $bin .= pack("a{$nameLen}a{$valueLen}", $name, $value);
    
        return $bin;
    }
    
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_connect($sock, FCGI_HOST, FCGI_PORT);
    
    $body   = getBeginRequestBody();
    $paddingLength = getPaddingLength($body);
    $header = getHeader(FCGI_BEGIN_REQUEST, FCGI_REQUEST_ID, strlen($body), $paddingLength, 0);
    $record = $header . $body . getPaddingData($paddingLength);
    socket_write($sock, $record);
    
    $env    = array(
        'SCRIPT_FILENAME' => FCGI_SCRIPT_FILENAME,
        'REQUEST_METHOD'  => FCGI_REQUEST_METHOD,
        'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
    );
    
    foreach ($env as $key=>$value)
    {
        $body          = getNameValue($key, $value);
        $paddingLength = getPaddingLength($body);
        $header        = getHeader(FCGI_PARAMS, FCGI_REQUEST_ID, strlen($body), $paddingLength, 0);
        $record        = $header . $body . getPaddingData($paddingLength);
        socket_write($sock, $record);
    }
     
    
    $body          = "";
    $paddingLength = getPaddingLength($body);
    $header        = getHeader(FCGI_STDIN, FCGI_REQUEST_ID, 0, $paddingLength, 0);
    $record        = $header . $body . getPaddingData($paddingLength);
    socket_write($sock, $record);
    
    $body          = "";
    $paddingLength = getPaddingLength($body);
    $header        = getHeader(FCGI_STDIN, FCGI_REQUEST_ID, 0, $paddingLength, 0);
    $record        = $header . $body . getPaddingData($paddingLength);
    socket_write($sock, $record);
    
    $header = socket_read($sock, 8);
    $header = unpack("Cversion/Ctype/nrequestId/ncontentLength/CpaddingLength/Creserved", $header);
    print_r($header);
    socket_close($sock);
```

</font>

[1]: https://my.oschina.net/goal/home

[5]: http://my.oschina.net/goal/blog/195749
[6]: http://www.fastcgi.com/devkit/doc/fcgi-spec.html
[7]: http://www.itcoder.me/?p=235
[8]: ../img/224336_6l8h_182025.jpg
[9]: http://www.itcoder.me
[10]: ../img/232850_tKCW_182025.jpg
[11]: https://github.com/adoy/PHP-FastCGI-Client/