# [PHP获取IP地址的方法,防止伪造IP地址注入攻击][0]

PHP获取IP地址的方法
```php
/**
 * 获取客户端IP地址
 * <br />来源：ThinkPHP
 * <br />"X-FORWARDED-FOR" 是代理服务器通过 HTTP Headers 提供的客户端IP。代理服务器可以伪造任何IP。
 * <br />要防止伪造，不要读这个IP即可（同时告诉用户不要用HTTP 代理）。
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false) {
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos)
                unset($arr[$pos]);
            $ip = trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证, 防止通过IP注入攻击
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 获得用户的真实IP地址
 * <br />来源：ecshop
 * <br />$_SERVER和getenv的区别，getenv不支持IIS的isapi方式运行的php
 * @access  public
 * @return  string
 */
function real_ip() {
    static $realip = NULL;
    if ($realip !== NULL) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr AS $ip) {
                $ip = trim($ip);

                if ($ip != 'unknown') {
                    $realip = $ip;

                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    // 使用正则验证IP地址的有效性，防止伪造IP地址进行SQL注入攻击
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}
```

**X-Forwarded-For地址形式列举：**  
unkonwn, unknown, 211.100.22.30  
172.16.20.110, 202.116.64.196, 203.81.21.61  
10.194.75.83, 10.194.73.11, 10.194.71.11, unknown  
192.168.120.57, unknown, unknown, 211.10.10.195  
unknown, 210.75.1.181  
155.161.59.47, unknown

**伪造IP地址进行注入攻击：**

IP伪造有几种途径，一种是通过是修改IP数据包，有兴趣的可以去看看IP数据包的结构，还有一种就是利用修改http头信息来实现IP伪造。涉及到“客户端”IP的通常使用3个环境变量：`$_SERVER['HTTP_CLIENT_IP']`和`$_SERVER['X_FORWARDED_FOR']`还有`$_SERVER['REMOTE_ADDR']`实际上，这3个环境变量都有局限性。前两个是可以随意伪造。只要在发送的http头里设置相应值就可以，任意字符都可以，而第3个环境变量，如果用户使用了匿名代理，那这个变量显示的就是代理IP。  
一般获取IP后更新到数据库代码如：$sql="update t_users set login_ip='".get_client_ip()."' where ..."，而如果接收到的ip地址是：xxx.xxx.xxx.xxx';delete from t_users;-- ，代入参数SQL语句就变成了："update t_users set login_ip='xxx.xxx.xxx.xxx';delete from t_users;-- where ...  
 所以获取IP地址后，务必使用正则等对IP地址的有效性进行验证，另外一定要使用参数化SQL命令！

## 总结：

**`重要的总结`**

"X-FORWARDED-FOR" 是**`代理服务器通过 HTTP Headers 提供的客户端IP`**。代理服务器可以伪造任何IP。  
要防止伪造，不要读这个IP即可（同时告诉用户不要用HTTP 代理）。  
如果是PHP，`$_SERVER['REMOTE_ADDR']` 就是**`跟你服务器直接连接的IP`**，用这个就可以了。

获取服务器IP地址：

```php
/**
 * 获取服务端IP地址
 * @return string
 * @since 1.0 2016-7-1 SoChishun Added.
 */
function get_host_ip() {
    return isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
}
```

**参考文章：**

http://www.cnblogs.com/skiplow/archive/2011/07/20/2111751.html (伪造IP地址进行SYN洪水攻击)  
http://www.feifeiboke.com/pcjishu/3391.html (你所不懂的火狐浏览器妙用之伪造IP地址)  
https://segmentfault.com/q/1010000000095850#a-1020000000098537 (如何避免用户访问请求伪造ip)  
http://blog.csdn.net/clh604/article/details/9234473 (PHP使用curl伪造IP地址和header信息)  
http://www.cnblogs.com/lmule/archive/2010/10/15/1852020.html (REMOTE_ADDR，HTTP_CLIENT_IP，HTTP_X_FORWARDED_FOR)

[0]: http://www.cnblogs.com/sochishun/p/7168860.html