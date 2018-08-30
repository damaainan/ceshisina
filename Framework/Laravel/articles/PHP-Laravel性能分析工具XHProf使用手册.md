## PHP/Laravel性能分析工具XHProf使用手册

来源：[https://haofly.net/xhprof/](https://haofly.net/xhprof/)

时间 2018-02-12 09:32:00


在公司一直用PHP作为主要开发语言，前期一直在不停的加需求该需求，年底终于有时间进行一波优化了。或许，PHP已经不再流行了，居然没有一个免费且好用的性能分析工具。这里有一份2015年的    [PHP性能分析工具的对比][0]
，功能上，不得不说老牌的`XHProf`依然是最强大的，并且比收费的做得更好，然而，该工具已经好几年没更新了，只有`Github`上面的几个`fork`，当然也有支持`PHP7`的。基于其功能强大、开源免费并且配置相对其他来说比较简单，我还是不得不选择它。  


## XHProf安装  


#### XHProf主程序安装  

```sh
# for php5.x
apt-get update && apt-get install vim wget php-pear php5-dev php5-mcrypt pkg-config libssl-dev graphviz -y
php5enmod mcrypt

pecl install xhprof-beta && echo "extension=xhprof.so" > /etc/php5/mods-available/xhprof.ini && php5enmod xhprof && php --ri xhprof	 # 最后可以看到xhprof的版本信息

```


#### XHProf UI界面安装  

```sh
cd /tmp && wget http://pecl.php.net/get/xhprof-0.9.4.tgz && tar zxvf xhprof-0.9.4.tgz	# 这就是其UI的主程序，同样是一个PHP程序，可以直接配置Nginx或者Apache指向其目录xhprof_html

# for Laravel，由于我是在容器中使用laravel，所以直接把ui程序放到public下面，十分方便。到时候直接访问domain/xhprof_html/index.html即可
cp -r xhprof-0.9.4/xhprof_html /data/www/html/kunkka/public/ && cp -r xhprof-0.9.4/xhprof_lib /data/www/html/kunkka/public/

```

需要注意的是，UI默认会去找`/tmp`目录下的分析结果。  


## XHProf使用  


#### Laravel  

网上有一些方法是编写中间件，我也试过，但是那样的分析结果并不是我想要的，时间也基本对不上。后来我就来得很直接，放到了整个项目的入口`public/index.php`:  

```php
<?php
require __DIR__.'/../bootstrap/autoload.php';

# 之所以在这里加，不在上面一步加，是因为上面一步以后才能使用最基本的一些方法函数
$beginXhprofTime = \MyHelpers\XhprofHelper::beginXhprof();

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);

# 结束分析
\MyHelpers\XhprofHelper::endXhprof($beginXhprofTime, env('APP_NAME', 'test'), env('XHPROF_MIN_TIME', 200));

```

`XhprofHelper`方法详细如下:  

```php
<?php
namespace MyHelpers;

class XhprofHelper
{
    const LOG_FILE_TYPE = 'xlog';
    const XHPROF_LOG_PATH = '/var/log/xhprof_log';

    /**
*@paramint $rate 频率，程序框架还未载入完成，此时还不能用env
*@returnmixed|null
*/
    static public function beginXhprof($rate =100)
{
        if (extension_loaded('xhprof') &&
            file_exists(self::XHPROF_LOG_PATH) &&
            rand(1, $rate) == ceil($rate / 2)
        ) {
            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
            return microtime();
        }
        return null;
    }

    /**
*@paramnull $xhprofBeginTime
*@paramstring $appName
*@paramint $minTime 记录的响应事件最小值，超过该值才记录
*/
    static public function endXhprof($xhprofBeginTime=null, $appName='test', $minTime=200)
{
        if (empty($xhprofBeginTime)) {
            return;
        }

        $xhprofData = xhprof_disable();

        $interval = intval((microtime() - $xhprofBeginTime) * 1000);

        if ($interval > $minTime) {
            self::saveRun($xhprofData, sprintf('%s-%s-%sms',
                $appName,
                date('YmdHis'),
                $interval));
        }
    }

    /**
* 保存结果，到指定文件夹
*/
    static private function saveRun($xhprofData, $runId)
{
        $xhprofData = serialize($xhprofData);

        $file = fopen(sprintf('%s/%s.%s', self::XHPROF_LOG_PATH, $runId, self::LOG_FILE_TYPE), 'w');
        if ($file) {
            fwrite($file, $xhprofData);
            fclose($file);
        } else {
            logger('Could not open ' . $runId);
        }

        return $runId;
    }
}

```

不看不知道，一看吓一跳，分析过很多的接口，我们自己写的逻辑代码基本上都没有什么性能上的问题，但是`Laravel`项目光启动就花了几十毫秒，调用链长的我的生成流程图都卡了。后来发现，几十一个什么逻辑都没写，什么依赖都装的纯laravel项目，在启动时候也是非常耗时的。而且我们项目好几个组件做成的为服务，一个请求如果调用几个组件，那时间就是成倍的增长。针对laravel项目，我觉得能优化的主要是这三点：  



* 升级到最新  `Laravel`，即使是升级稳定版，最新的稳定版也肯定比老的稳定版效率更高。    
* 升级到  `PHP7`，  `PHP7`的性能真不是吹的。    
* 使用  `Swoole`，PHP的异步编程框架。最大的优点，不用每次请求都重启整个框架了。性能提升指数比上面两者都大。    
  



[0]: https://sandro-keil.de/blog/2015/02/10/php-profiling-tools/