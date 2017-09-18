# PHP读取HTTP接口如何处理超时

2017.03.07 11:15  字数 1249 

最近在工作中遇到一个读取 HTTP 接口慢的问题（使用的是 PHP 服务器端语言），所以想谈谈服务器端读取外部资源超时机制的问题，谨以此文做个笔记。

在 Web 开发中，需要有大量的外部资源进行交互，比如说 Mysql、Redis、Memcached、HTTP 接口，这些资源具备这样一些特点：

* 都是网络接口
* 这些资源的可用性，连接速度、读取速度不可控
* 分层模式，对于调用方来说，只明确是否能够读取数据、数据是否正确；对于资源提供方来说负责具体的数据逻辑。

对于资源的调用方来说，个人建议有以下的处理原则:

* 超时机制：读取的资源假如特别慢，那么应该有读取超时机制，对于应用程序来说，一个 HTTP 接口，假如返回数据需要十秒，本身是不可接受的。
* 重试机制：假如一个资源特别重要，比如说这个资源获取不到，但应用程序逻辑严重依赖它，为了尽可能保持可用，可以进行重试读取资源。
* 异常处理机制，就是说资源获取不到，应该抛出一个异常，而不是一个警告，PHP 由于历史原因不强调异常机制，所以很多程序其实都是错误的，举个例子，访问 HTTP 接口超时，很多开发者武断的就认为返回数据为空，这是一个严重的逻辑错误。另外超时也是异常的一部分。

本文主要谈谈服务器程序读取 HTTP 接口超时机制问题，为什么强调服务器程序，主要是因为客户端 JavaScript 读取 HTTP 接口在处理机制上有很大的不同（或者说应用场景不同）。

#### 超时应该设置多少

超时可以细分为连接超时和读取超时，设置多少，取决于两方面，第一是 HTTP 接口的承若，比如说微信公众平台接口，其速度和可用性要求应该是极高的，虽然官方没有说明，但是我相信对于微信内部来说，单个接口响应速度不可能超过 1 秒。第二就是使用者的考虑，比如说队列程序读取接口超时可以设置高一点，而其他程序相应超时时间不能设置太长，取决于**程序、应用的性质和服务能力**。

说句题外话，假如 HTTP 接口出现故障，响应很慢，但是你的程序调用超时设置很大（假如再加上重试），就会进一步加重 HTTP 接口服务的可用性，可能会形成雪崩效应。

#### default_socket_timeout

那么如何设置超时呢，PHP 流机制可以通过 default_socket_timeout 指令来配置。  
流是 PHP 中很重要的一个特性，以后可以说一说，简单的理解就是在 PHP 中，不管是读取磁盘文件、HTTP 接口，都可以认为是一种流（socket/stream）。

说明下， socket/stream 的等待时间是不包括在 PHP 最大执行时间内的。  
比如说在 PHP.ini 中 配置 max_execution_time = 30，max_execution_time = 20，那么这个 PHP 程序最大处理执行时间是 50 秒。

现在重点来了，原来自己认为**超时时间假如为 m 秒，那么访问接口最终响应（包括网络传输时间）超过 m 秒，调用程序就会报错。实际并不是这样，只要在 m 秒数据包一直在传输，那么调用程序就不会报错。**

通过程序来演示下，先看接口代码，模拟网络传输慢的情况：

    ob_implicit_flush(1);
    for($i=0; $i<6; $i++){
        echo $i; 
        echo str_repeat(' ',1024*64);
        sleep(1);
    }

现在看看调用代码，可以看出虽然接口最后输出需要 6 秒，但由于数据库包一直在传输，代码并不报错。

    ini_set("default_socket_timeout", 3);
    $url = "http://localhost/api.php";
    
    function e_filegetcontents() {
        global $url;
        var_dump(file_get_contents($url));
    }
    
    function e_fopenfgets(){
        global $url;
        $context = stream_context_create(array('http'=> array(
        'timeout' => 3.0,
        ))); 
    
        $handle = fopen($url, "r",true,$context);
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
            }
            fclose($handle);
        }
    }
    
    e_filegetcontents();
    e_fopenfgets();

#### 还是让我们使用 cURL 扩展来处理超时控制吧

假如你想更精确的处理超时，就使用 cURL 扩展，它可以设置连接超时和读取超时（CURLOPT_TIMEOUT，CURLOPT_CONNECTTIMEOUT）。

假如希望控制 HTTP 接口必须在毫秒级别返回，还可以使用 CURLOPT_TIMEOUT_MS and CURLOPT_CONNECTTIMEOUT_M 常量。  
注意假如使用这两个常量，必须设置 curl_setopt($ch, CURLOPT_NOSIGNAL, 1);神奇的来了，cURL 扩展机制很特别，**在指定的读取时间获取到多少数据就返回多少，然后调用也终止，程序并不报错**

通过代码看一下：

    function e_curl() {
        global $url;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $response = curl_exec($ch);
        if ($response === false) {
            $info = curl_getinfo($ch);
            if ($info['http_code'] === 0) {
            return false;
            }
        }
        return true;
    }
    e_curl();
