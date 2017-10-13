转自 [https://github.com/hirudy/phplib](https://github.com/hirudy/phplib) ，**感谢作者**

实际使用总结出来的通用phpLib

# 1.TLogger
日志记录工具

```php
interface ILoggerHandle{
    // 解析配置文件,看配置文件是否满足需求
    public static function parseConfig($rawConfig);

    // 不同级别日志记录函数
    public function fatal($message);
    public function error($message);
    public function warn($message);
    public function info($message);
    public function debug($message);
}
```
对外提供的函数,总共有5种日志级别,等级依次降低

## 配置样例
```php
    array(
        'name' => 'default',                           // 日志名称,全局唯一
        'isLogging' => true,                           // 当前日志是否记录
        'basePath' => TLogger::$g_basePath,            // 当前日志的记录根目录,没有,默认全局目录:g_basePath
        'mode' => TLogger::LOG_MODE_FILE,              // 记录模式
        'level' => TLogger::LOG_LEVEL_DEBUG,           // 日志等级
        'frequency' => TLogger::LOG_FREQUENCY_NONE,    // 切割日志方式
    )
```

## 使用举例
```php
    $config = array(  // 日志配置文件数组,default是默认配置项
        'name' => 'test',
        'level' => TLogger::LOG_LEVEL_INFO,
        'frequency' => TLogger::LOG_FREQUENCY_MINUTE
    );
    TLogger::$g_basePath = __DIR__.DIRECTORY_SEPARATOR.'log';
    TLogger::loadOneConfig($config);

    $logger = TLogger::getLogger("test");
    $logger->debug("this is debug info ");
    $logger->info(array("is","info","recode"));
    $logger->warn(21);
    $logger->error("error info ");
    $logger->fatal($logger);
```

# 2.THttp
封装的http请求类

## 对外提供方法
```php
class THttp{
    /**
     * 通用请求方法
     * @param string $url 请求url地址
     * @param array  $postData 请求post参数数组
     * @param array  $header  请求附带请求头部数组
     * @param int    $timeOut 超时时间
     * @param string $proxy 代理设置
     * @return array
     */
    public static function request($url,$postData=array(),$header=array(),$timeOut=self::DEFAULT_TIMEOUT, $proxy='');

    // 简单返回请求方法,相对于THttp::request(),简化了返回结果
    public static function simpleResponseRequest($url,$postData=array(), $header=array(), $timeOut=self::DEFAULT_TIMEOUT, $proxy='');

    // 多个http请求并行执行
    public static function multiRequest(Array $requestList);
}    
```
## 调用实例
```php
    // 串行请求
    $start_time = microtime(true);
    $response1 = THttp::request('https://www.baidu.com/');
    $response2 = THttp::request('http://www.jd.com');
    $response3 = THttp::request('http://www.jianshu.com/');
    $response4 = THttp::request('http://www.zhihu.com/');
    $response5 = THttp::request('http://www.php.net/');
    $response6 = THttp::request('https://github.com/hirudy');
    $response7 = THttp::request('http://www.toutiao.com/');
    $response8 = THttp::request('http://www.mi.com/');
    //    $response9 = THttp::request('https://www.google.com');
    echo "serial request take time : ", microtime(true)-$start_time,"\n";

    // 并行请求
    $start_time = microtime(true);
    $responseList = THttp::multiRequest(array(
        array('url'=>'https://www.baidu.com/'),
        array('url'=>'http://www.jd.com'),
        array('url'=>'http://www.jianshu.com/'),
        array('url'=>'http://www.zhihu.com/'),
        array('url'=>'http://www.php.net/'),
        array('url'=>'https://github.com/hirudy'),
        array('url'=>'http://www.toutiao.com/'),
        array('url'=>'http://www.mi.com/'),
//        array('url'=>'https://www.google.com')
    ));
    echo "parallel requests take time : ", microtime(true)-$start_time,"\n";
```

# 3.Image
一些图片相关操作封装

# 4.MysqlDB
mysql操作类，需要mysqli扩展

# 5.phpAb
php实现的压测工具
