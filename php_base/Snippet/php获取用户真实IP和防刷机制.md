## php获取用户真实IP和防刷机制

来源：[https://www.cnblogs.com/saysmy/p/10006762.html](https://www.cnblogs.com/saysmy/p/10006762.html)

2018-11-26 18:00

 

## 一. 如何获取用户IP地址

```php
    public static function getClientIp()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        if (getenv('HTTP_X_REAL_IP')) {
            $ip = getenv('HTTP_X_REAL_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            $ips = explode(',', $ip);
            $ip = $ips[0];
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = '0.0.0.0';
        }

        return $ip;
    }
```

  **`注意：`** 

```
$_SERVER和getenv的区别，getenv不支持IIS的isapi方式运行的phpgetenv(“REMOTE_ADDR”)函数在 apache下能正常获取ip地址，而在iis中没有作用，而$_SERVER['REMOTE_ADDR']函数，既可在apache中成功获取访客的ip地址，在iis下也同样有效
```

```
一、关于 REMOTE_ADDR
这个变量获取到的是《直接来源》的 IP 地址，所谓《直接来源》指的是直接请求该地址的客户端 IP 。这个 IP 在单服务器的情况下，很准确的是客户端 IP ，无法伪造。当然并不是所有的程序都一定是单服务器，比如在采用负载均衡的情况（比如采用 haproxy 或者 nginx 进行负载均衡），这个 IP 就是转发机器的 IP ，因为过程是客户端->负载均衡->服务端。是由负载均衡直接访问的服务端而不是客户端。

二、关于 HTTP_X_FORWARDED_FOR 和 HTTP_CLIENT_IP
基于《一》，在负载均衡的情况下直接使用 REMOTE_ADDR 是无法获取客户端 IP 的，这就是一个问题，必须解决。于是就衍生出了负载均衡端将客户端 IP 加入到 HEAD 中发送给服务端，让服务端可以获取到客户端的真实 IP 。当然也就产生了各位所说的伪造，毕竟 HEAD 除了协议里固定的那几个数据，其他数据都是可自定义的。

三、为何网上找到获取客户端 IP 的代码都要依次获取 HTTP_CLIENT_IP 、 HTTP_X_FORWARDED_FOR 和 REMOTE_ADDR
基于《一》和《二》以及对程序通用性的考虑，所以才这样做。 假设你在程序里直接写死了 REMOTE_ADDR ，有一天你们的程序需要做负载均衡了，那么你有得改了。当然如果你想这么做也行，看个人爱好和应用场景。也可以封装一个只有 REMOTE_ADDR 的方法，等到需要的时候改这一个方法就行了。
```


 **`总结：`** 

 **``HTTP_CLIENT_IP: ``** 头是有的，只是未成标准，不一定服务器都实现了。

 **`X-Forwarded-For（XFF）: `** 是用来识别通过[HTTP][100][代理][101]或[负载均衡][102]方式连接到[Web服务器][103]的客户端最原始的[IP地址][104]的HTTP请求头字段, 格式：`clientip,proxy1,proxy2` **``REMOTE_ADDR:``**  是可靠的， 它是最后一个跟你的服务器握手的`IP`，可能是用户的代理服务器，也可能是自己的反向代理。


## X-Forwarded-For 和 X-Real-IP区别：

`X-Forwarded-For`是用于记录代理信息的，每经过一级代理(匿名代理除外)，代理服务器都会把这次请求的`来源IP`追加在`X-Forwarded-For`中, 而`X-Real-IP`，没有相关标准, 其值在不同的代理环境不固定

关于此的更多讨论可以参考：[https://www.douban.com/group/topic/27482290/][105] 


## 1. 负载均衡情况： 

![][0]

生产环境中很多服务器隐藏在负载均衡节点后面，你通过`REMOTE_ADDR`只能获取到负载均衡节点的ip地址，一般的负载均衡节点会把前端实际的ip地址通过`HTTP_CLIENT_IP`或者`HTTP_X_FORWARDED_FOR`这两种http头传递过来。

后端再去读取这个值就是真实可信的，因为它是负载均衡节点告诉你的而不是客户端。但当你的服务器直接暴露在客户端前面的时候，请不要信任这两种读取方法，只需要读取`REMOTE_ADDR`就行了

延伸阅读：[一张图解释负载均衡][106]


## 2. CDN的情况：

![][1]

所以对于我们获取用户的IP，应该截取http_x_forwarded_for的第一个有效IP(非unknown)。

多层代理时，和cdn方式类似。

 **`注意：`** 

无论是REMOTE_ADDR还是HTTP_FORWARDED_FOR，这些头消息未必能够取得到,因为不同的浏览器不同的网络设备可能发送不同的IP头消息


## 二. 防止IP注入攻击

加入以下代码防止IP注入攻击：

```php
// IP地址合法验证, 防止通过IP注入攻击
$long = sprintf("%u", ip2long($ip));
$ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
```

一般获取IP后更新到数据库代码如： $sql=" update t_users set login_ip=' " .get_client_ip()." ' where ... "   ，而如果接收到的ip地址是： xxx.xxx.xxx.xxx' ;delete from t_users;--    ，代入参数SQL语句就变成了： " update t_users set login_ip='xxx.xxx.xxx.xxx';delete from t_users;-- where ...   

所以获取IP地址后，务必使用正则等对IP地址的有效性进行验证，另外一定要使用参数化SQL命令


### **`解析：`** 

 **`sprintf()`**  函数把格式化的字符串写入变量中。

* %u - 不包含正负号的十进制数（大于等于 0）


 **`int ip2long ( string `$ip_address` ) :`** 

返回IP地址转换后的数字 或 `FALSE` 如果 `ip_address` 是无效的。

### **`注意 : `** 

例子说明打印一个转换后的地址使用 [printf()][107] 在PHP4和PHP5的功能:

```php
<?php
$ip   = gethostbyname('www.example.com');
$long = ip2long($ip);

if ($long == -1 || $long === FALSE) {
    echo 'Invalid IP, please try again';
} else {
    echo $ip   . "\n";           // 192.0.34.166
    echo $long . "\n";           // -1073732954
    printf("%u\n", ip2long($ip)); // 3221234342
}
?>
```

1. 因为PHP的 [integer][108] 类型是有符号，并且有许多的IP地址讲导致在32位系统的情况下为负数， 你需要使用 "%u" 进行转换通过 [sprintf()][109] 或[printf()][107] 得到的字符串来表示无符号的IP地址。

2. ip2long() 将返回 `FALSE` 在IP是  255.255.255.255  的情况，版本为 PHP 5 <= 5.0.2. 在修复后 PHP 5.0.3 会返回  -1  (与PHP4相同).


## 三. 防刷机制

对于获取到IP后我们可以做一些防刷操作：

```php
//ip限额
$ip = getClientIp();
$ipKey = "activity_key_{$ip}";
```

```php
if (!frequencyCheckWithTimesInCache($ipKey, $duration, $limitTimes)) {
    return false;
}
return true;
```

```php
// 限制id，在$second时间内，最多请求$times次    

public static function frequencyCheckWithTimesInCache($id, $second, $times)
    {
        $value = Yii::app()->cache->get($id);
        if (!$value) {
            $data[] = time();
            Yii::app()->cache->set($id, json_encode($data), $second);

            return true;
        }
        $data = json_decode($value, true);
        if (count($data) + 1 <= $times) {
            $data[] = time();
            Yii::app()->cache->set($id, json_encode($data), $second);

            return true;
        }

        if (time() - $data[0] > $second) {
            array_shift($data);
            $data[] = time();
            Yii::app()->cache->set($id, json_encode($data), $second);

            return true;
        }

        return false;
    }
```


 **`举例：`** 

 **`限制每小时请求不超过50次`** 

```php
if (!frequencyCheckWithTimesInCache('times_uid_' . $uid, 3600, 50)) {
            return '请求过于频繁';
        }
```


## 防刷升级限制设备号：

```php
//设备号 一个设备号最多只能抽奖3次
        if(! empty($deviceId)){
                $deviceUseChance = Yii::app()->db->createCommand()
                    ->select('count(id)')->from('activity00167_log')
                    ->where('device_id=:deviceId',['deviceId'=>$deviceId])
                    ->queryScalar();
                $deviceChance = 3 - $deviceUseChance;
        } 
```

对于获取IP地址还可以在大数据分析用户的地理位置，比如做一些精准投放等工作。

-----

以上有理解不正确的欢迎指出讨论~

参考文章：

https://www.cnblogs.com/iteemo/p/5062291.html

https://segmentfault.com/q/1010000000686700

http://www.cnblogs.com/sochishun/p/7168860.html

[0]: ../img/979473-20181126170409926-997287736.png
[1]: ../img/979473-20181126172333243-843489427.jpg
[100]: https://zh.wikipedia.org/wiki/HTTP
[101]: https://zh.wikipedia.org/wiki/%E4%BB%A3%E7%90%86%E6%9C%8D%E5%8A%A1%E5%99%A8
[102]: https://zh.wikipedia.org/wiki/%E8%B4%9F%E8%BD%BD%E5%9D%87%E8%A1%A1
[103]: https://zh.wikipedia.org/wiki/Web%E6%9C%8D%E5%8A%A1%E5%99%A8
[104]: https://zh.wikipedia.org/wiki/IP%E5%9C%B0%E5%9D%80
[105]: https://www.douban.com/group/topic/27482290/
[106]: https://www.cnblogs.com/saysmy/articles/10006793.html
[107]: http://php.net/manual/zh/function.printf.php
[108]: http://php.net/manual/zh/language.types.integer.php
[109]: http://php.net/manual/zh/function.sprintf.php
[110]: http://php.net/manual/zh/function.printf.php