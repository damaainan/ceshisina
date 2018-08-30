## LaravelS - 通过Swoole来加速 Laravel/Lumen

来源：[https://segmentfault.com/a/1190000013358289](https://segmentfault.com/a/1190000013358289)


## LaravelS - 站在巨人的肩膀上

🚀 通过Swoole来加速 Laravel/Lumen，其中的S代表Swoole，速度，高性能。
## 特性


* 高性能的Swoole
* 内置Http服务器
* 常驻内存
* 平滑重启
* 同时支持Laravel与Lumen，兼容主流版本
* 简单，开箱即用


## 要求

| 依赖 | 说明 |
|-|-|
| [PHP][0] | `>= 5.5.9` |
| [Swoole][1] | `>= 1.7.19``推荐最新的稳定版``从2.0.12开始不再支持PHP5` |
| [Laravel][2]/[Lumen][3] | `>= 5.1` |
| Gzip[可选的] | [zlib][4], 检查本机libz是否可用 **`ldconfig -p|grep libz`**  |


## 安装

1.通过[Composer][5]安装([packagist][6])

```
# 在你的Laravel/Lumen项目的根目录下执行
composer require "hhxsv5/laravel-s:~1.0" -vvv
# 确保你的composer.lock文件是在版本控制中
```

2.添加service provider

* `Laravel`: 修改文件`config/app.php`

```php
'providers' => [
    //...
    Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class,
],
```

* `Lumen`: 修改文件`bootstrap/app.php`

```php
$app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class);
```

3.发布配置文件

```
php artisan laravels publish
```

`特别情况`: 你不需要手动加载配置`laravels.php`，LaravelS底层已自动加载。

```php
// 不必手动加载，但加载了也不会有问题
$app->configure('laravels');
```

4.修改配置`config/laravels.php`：监听的IP、端口等，请参考[配置项][7]。
## 运行

`php artisan laravels {start|stop|restart|reload|publish}`

| 命令 | 说明 |
|-|-|
| `start` | 启动LaravelS，展示已启动的进程列表  **`ps -ef|grep laravels`**  |
| `stop` | 停止LaravelS |
| `restart` | 重启LaravelS |
| `reload` | 平滑重启所有worker进程，这些worker进程内包含你的业务代码和框架(Laravel/Lumen)代码，不会重启master/manger进程 |
| `publish` | 发布配置文件到你的项目中`config/laravels.php` |


## 与Nginx配合使用

```nginx
upstream laravels {
    server 192.168.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.0.2:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.0.3:5200 backup;
}
server {
    listen 80;
    server_name laravels.com;
    root /xxxpath/laravel-s-test/public;
    access_log /yyypath/log/nginx/$server_name.access.log  main;
    autoindex off;
    index index.html index.htm;
    
    # Nginx处理静态资源，LaravelS处理动态资源。
    location / {
        try_files $uri @laravels;
    }

    location @laravels {
        proxy_http_version 1.1;
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout 120s;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Host $host;
        proxy_pass http://laravels;
    }
}
```
## 监听事件

通常，你可以在这些事件中重置或销毁一些全局或静态的变量，也可以修改当前的请求和响应。
* `laravels.received_request` 将`swoole_http_request`转成`Illuminate\Http\Request`后，在Laravel内核处理请求前。

```php
// 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
// 如果变量$exents不存在，你也可以调用\Event::listen()。
$events->listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $req->query->set('get_key', 'hhxsv5');// 修改querystring
    $req->request->set('post_key', 'hhxsv5'); // 修改post body
});
```

* `laravels.generated_response` 在Laravel内核处理完请求后，将`Illuminate\Http\Response`转成`swoole_http_response`之前(下一步将响应给客户端)。

```php
$events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp) {
    $rsp->headers->set('header-key', 'hhxsv5');// 修改header
});
```
## 在你的项目中使用`swoole_http_server`实例

```php
/**
* @var \swoole_http_server
*/
$swoole = app('swoole');// Singleton
var_dump($swoole->stats());
```
## 注意事项

* 推荐通过`Illuminate\Http\Request`对象来获取请求信息，兼容$_SERVER、$_GET、$_POST、$_FILES、$_COOKIE、$_REQUEST，`不能使用`$_SESSION、$_ENV。

```php
public function form(\Illuminate\Http\Request $request)
{
    $name = $request->input('name');
    $all = $request->all();
    $sessionId = $request->cookie('sessionId');
    $photo = $request->file('photo');
    $rawContent = $request->getContent();
    //...
}
```

* 推荐通过返回`Illuminate\Http\Response`对象来响应请求，兼容echo、vardump()、print_r()，`不能使用`函数像exit()、die()、header()、setcookie()、http_response_code()。

```php
public function json()
{
    return response()->json(['time' => time()])->header('header1', 'value1')->withCookie('c1', 'v1');
}
```


* 你声明的全局、静态变量必须手动清理或重置。
* 无限追加元素到静态或全局变量中，将导致内存爆满。


```php
// 某类
class Test
{
    public static $array = [];
    public static $string = '';
}

// 某控制器
public function test(Request $req)
{
    // 内存爆满
    Test::$array[] = $req->input('param1');
    Test::$string .= $req->input('param2');
}
```

**`如果对你有帮助，Star Me [LaravelS][8]`** 
## TODO 持续更新


* 针对MySQL/Redis的连接池。
* 包装MySQL/Redis/Http的协程客户端。
* 针对Swoole`2.1+` 自动的协程支持。


[0]: https://secure.php.net/manual/zh/install.php
[1]: https://www.swoole.com/
[2]: https://laravel.com/
[3]: https://lumen.laravel.com/
[4]: https://zlib.net/
[5]: https://getcomposer.org/
[6]: https://packagist.org/packages/hhxsv5/laravel-s
[7]: https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md
[8]: https://github.com/hhxsv5/laravel-s