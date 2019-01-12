## php workerman定时任务

来源：[http://www.jianshu.com/p/b2edf994555b](http://www.jianshu.com/p/b2edf994555b)

时间 2018-12-19 20:56:26

 
## 一、下载workerman

```
https://www.workerman.net/download
```
 
## 二、下载workerman/mysql

```
http://doc3.workerman.net/640201
```

![][0]

 
1544699587(1).jpg

 
shipments.php用来写定时任务

```php
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/29
 * Time: 16:59
 */

use Workerman\Worker;
use \Workerman\Lib\Timer;

require_once "Workerman/Autoloader.php";


require_once "Connection.php";

$task = new Worker();

$task->onWorkerStart = function ($task) {

    global $db, $redis;
    $db    = new \Workerman\MySQL\Connection('127.0.0.1', '3306', 'root', 'root', 'test');
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->auth("qqq123123.");
    $time_interval = 0.1;
    Timer::add($time_interval, function () {
        global $db, $redis;
        
        $insert['name'] = 123;
        
        $db->insert('shipments')->cols($insert)->query();

//        sleep(100);
    });

};


function curlGet($url = '', $options = [])
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if (!empty($options)) {
        curl_setopt_array($ch, $options);
    }
    //https请求 不验证证书和host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function newGetOrderInfo($taobao, $orderId)
{
    $taobao = urlencode($taobao);
    $url    = "http://114.55.144.79/taobao/TradeFullinfoGetRequest.php?shop=$taobao&tid=$orderId";
    $json   = curlGet($url);
    return json_decode($json, true)['trade'];
}

Worker::runAll();
```


[0]: https://img2.tuicool.com/iaYBJre.png