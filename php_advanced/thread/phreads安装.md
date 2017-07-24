# [windows下安装PHP的pthreads多线程扩展][10]

我的运行环境：

系统：windows10 ,64位

pthreads的windows扩展文件下载地址：http://windows.php.net/downloads/pecl/releases/pthreads/


安装步骤：

1，将pthreadVC2.dll复制到 X盘:\wamp\php\
2，将php_pthreads.dll复制到 X盘:\wamp\php\ext\
3，php.ini添加extension=php_pthreads.dll
4， 修改Apache配置文件httpd.conf 添加  **LoadFile "X盘:/wamp/php/pthreadVC2.dll"**
5，重启apache
官方测试代码：

```php
    <?php
    class AsyncOperation extends Thread {
      public function __construct($arg){
        $this->arg = $arg;
      }
    
      public function run(){
        if($this->arg){
          printf("Hello %s\n", $this->arg);
        }
      }
    }
    $thread = new AsyncOperation("World");
    if($thread->start())
      $thread->join();
    ?>
```

## windows 下 php 添加 pthreads 后，apache 无法启动怎么解决？？

该问题已解决。  
pthreads 模块无法在 web 模式下运行（apache | nginx之类的服务器上）。只允许在 php-cli 模式下运行。

解决办法：创建两个配置文件（解决，web模式下添加 pthreads 扩展出错）。

    php.ini        # web 模式下会自动加载
    php-cli.ini    # php-cli 模式下回自动加载

详情看：  
pthreads 扩展安装相关问题：[https://segmentfault.com/q/10...][0]  
pthreads 官网手册：[http://php.net/manual/zh/pthr...][1]  
php-cli 官网手册：[http://php.net/manual/zh/feat...][2]

#### 支持 PHP 7 的 pthreads v3 只支持通过 cli 命令行来调用，不支持其他的 sapi

CLI下PHP默认会优先读取php-cli.ini,如果没有则读取php.ini,执行php --ini可以看到PHP使用的配置,另外可以通过-c参数指定配置文件.  

[http://segmentfault.com/q/1010000002577629/a-1020000002630826][3]

[0]: https://segmentfault.com/q/1010000004327568
[1]: http://php.net/manual/zh/pthreads.requirements.php
[2]: http://php.net/manual/zh/features.commandline.php
[3]: http://segmentfault.com/q/1010000002577629/a-1020000002630826
[4]: [10]: http://www.cnblogs.com/yuanfeiblog/p/5723699.html

