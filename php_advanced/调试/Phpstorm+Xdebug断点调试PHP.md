## Phpstorm+Xdebug断点调试PHP

来源：[http://www.cnblogs.com/52fhy/p/9031293.html](http://www.cnblogs.com/52fhy/p/9031293.html)

时间 2018-05-13 10:14:00

 
## 为什么使用断点调试
 
大家可能会觉得使用var_dump和echo也能调试啊，为什么还要安装Xdebug断点调试呢？
 
确实是这样。但是var_dump和echo写的代码后面还要删除，而且明确是知道在哪打的，如果发现没有运行到打点的地方，还要修改代码再运行一次。而断点调试，可以在调试过程中动态打断点，逐行查看当前各个变量的值，甚至临时修改变量的值，更方便。建议大家使用Xdebug断点调试。
 
## 安装Xdebug
 
```
pecl install xdebug
```
 
如果是php7以下版本，需要加上版本号：
 
```
pecl install xdebug-2.5.5
```
 
pecl如果提示找不到该扩展，则使用源码编译。例如：
 
```
wget http://pecl.php.net/get/xdebug-2.5.5.tgz \
    && tar xzf xdebug-2.5.5.tgz && cd xdebug-2.5.5/ \
    && phpize \
    && ./configure \
    && make && make install
```
 
注：php5.6只能使用2.5及以下版本xdebug。
 
安装好后需要在php.ini进行配置：
 
```
[xdebug]
zend_extension=xdebug.so
xdebug.enable=1
xdebug.remote_enable=1
;如果开启此,将忽略下面的 xdebug.remote_host 的参数
;xdebug.remote_connect_back=1
;自动启动，无需XDEBUG_SESSION_START=1
xdebug.remote_autostart=1 
;宿主机IP
xdebug.remote_host=192.168.36.101 
xdebug.remote_port=19001
xdebug.remote_handler=dbgp
```
 
需要注意的是：
 
1、是zend_extension，不是extension；
 
2、`xdebug.remote_autostart`开启后，就不用手动在请求url里加上`XDEBUG_SESSION_START=1`了，只要Phpstorm开启断点调试就ok了。很方便；
 
3、`remote_host`配置的是安装有Phpstorm的机器，这点需要注意。如果php安装的机器和Phpstorm安装的机器是同一台机器，那么地址写`127.0.0.1`即可。
 
如果你采用的是docker环境，`remote_host`写宿主机的IP。
 
4、`xdebug.remote_port`端口我写的是`19001`，那么Phpstorm也需要修改。
 
配置完成后需要重启php-fpm。
 
## 配置Phpstorm
 
  
配置也很简单，配置端口即可：
 
  
![][0]
 
 
 
  
接下来就可以断点调试了。开启监听：
 
  
![][1]
 
请求url的时候就会自动捕捉到请求。
 
 
 
  
注意：
 
1、不要同时开启多个项目的监听；
 
2、监听远程代码的时候，如果宿主机和代码所在目录结构一致，会直接监听成功。否则，会提示设置代码映射关系。也可以手动设置：
 
  
![][2]
 
 
 
这里因为宿主机是windows，代码在linux里，目录不一致，做了映射。否则断点会失败。
 


[0]: ../img/An6Nza3.png 
[1]: ../img/R7RnAbm.png 
[2]: ../img/AjaUbeZ.png 