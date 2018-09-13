## PHP使用Zend Opcache之优化加速和缓存清理总结

来源：[http://www.cnblogs.com/lishanlei/p/9548884.html](http://www.cnblogs.com/lishanlei/p/9548884.html)

时间 2018-08-28 16:08:00

## 简介

字节码缓存不是php的新特性，有很多独立性的扩展可以实现缓存，比如PHP Cache（APC），eAccelerator，ionCube和XCache等等。但是到目前为止，这些独立的扩展并没有集成到php核心当中。所有在php5.5.0之后，php内置了字节码缓存功能，叫做Zend Opcache。

zend Opcache前身是Zend Optimizer +，在03年改名Opcache，通过opcode缓存和优化提供更快的PHP执行过程。他会将预编译后的php文件存储在共享内存中以供以后的使用，避免从磁盘读取文件在进行解释的重复过程，减少时间和内存的消耗。在php5.5中及之后的版本中自带了zend opcache模块扩展，但是需要使用时需要我们开启和配置。在php5.2- 5.4版本我们也可以使用Opcache，但是需要我们自行安装。


## 什么是操作码缓存

那么什么是字节码缓存呢？php是一门解释型的语言，php解释器执行php脚本时会解析php脚本代码，将php脚本代码编译成一系列可以直接运行的中间代码，也称为操作码（Operate Code，opcode）。然后执行这些操作码．

每次请求php文件都是这样，所以会消耗很多资源，如果每次ＨＴＴＰ请求ＰＨＰ都必须解析，编译和运行脚本，消耗的资源将会更多．

Opcode cache 的目地是避免重复编译，减少 CPU 和内存开销。需要注意的是如果动态内容的性能瓶颈不在于 CPU 和内存，而在于 I/O 操作，比如数据库查询带来的磁盘 I/O 开销，那么 opcode cache 的性能提升是非常有限的．

现代操作码缓存器（Optimizer+，APC2.0+，其他）使用共享内存进行存储，并且可以直接从中执行文件，而不用在执行前“反序列化”代码。这将带来显着的性能加速，通常降低了整体服务器的内存消耗，而且很少有缺点．


## 安装（php5.5.0以上跳过）

在PHP 5.5.0及之后版本中，PHP已经将Opcache功能以拓展形式内嵌在发布版本中了，默认未开启Opcache加速，需要我们手动开启。对于之前的老版本，可以将Opcache作为PECL拓展库进行安装和配置．


### window下的安装

1. 下载扩展:    [https://windows.php.net/downloads/pecl/releases/opcache/][0]

2. 将php_opcache.dll放进php/ext目录下

3. 修改php.ini下[php]配置（注意路径要修改成你自己的）：

```ini
[php]
engine = On
extension = php_opcache.dll
zend_extension = "c:/xxx/php/ext/php_opcache.dll"
```


4. 添加php.ini下的 **`[opcache]`** 配置：       

```ini
[opcache] 

opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1
```

5. 重启apache服务，检查opcache是否开启成功   


### linux下的安装


#### 源码安装

```
wget http://pecl.php.net/get/zendopcache-7.0.5.tgz
tar zxvf zendopcache-7.0.5.tgz
cd zendopcache-7.0.5
/path/to/php/bin/phpize
./configure --with-php-config=/path/to/php/bin/php-config
make && make install
```

在php.ini下的[php]添加如下配置：   

```ini
zend_extension=php_opcache.so
```

在php.ini的[opcache]下添加：   

```ini
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1
```
 **`pecl 版本安装`**        

```
yum install php-pecl-zendopcache
```

安装时产生的 opcache 的配置文件位于默认的 /etc/php.d 目录中：   

```
opcache-default.blacklist 
opcache.ini
```

修改该配置：   

```
vi /etc/php.d/opcache.ini  
```

对照修改：   

```ini
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1
```

不需要修改php.ini，重启apache服务．   


## 常用配置

```ini
;开关打开
opcache.enable=1

;开启CLI
opcache.enable_cli=1

;可用内存, 酌情而定, 单位为：Mb
opcache.memory_consumption=528

;Zend Optimizer + 暂存池中字符串的占内存总量.(单位:MB)
opcache.interned_strings_buffer=8

;对多缓存文件限制, 命中率不到 100% 的话, 可以试着提高这个值
opcache.max_accelerated_files=10000

;Opcache 会在一定时间内去检查文件的修改时间, 这里设置检查的时间周期, 默认为 2, 定位为秒
opcache.revalidate_freq=1

;打开快速关闭, 打开这个在PHP Request Shutdown的时候回收内存的速度会提高
opcache.fast_shutdown=1

;检查脚本时间戳是否有更新的周期，以秒为单位。设置为 0 会导致针对每个请求， OPcache 都会检查脚本更新。
opcache.revalidate_freq=0  

;如果启用，那么 OPcache 会每隔 opcache.revalidate_freq 设定的秒数 检查脚本是否更新。
;如果禁用此选项，你必须使用 opcache_reset() 或者 opcache_invalidate() 函数来手动重置 OPcache，也可以 通过重启 Web 服务器来使文件系统更改生效。
opcache.validate_timestamps=0  
```

注意：如果设置opcache的opcache.validate_timestamps的指令设成０，那么zend opcache就察觉不到ＰＨＰ脚本的变化，我们必须手动清空zend opcache缓存的字节码，让他发现php脚本的变动．这个设置适合在生产环境中设置成０，在开发环境下最好还是设置成1．   

我们可以这样配置，启用自动重新验证缓存功能：

```ini
opcache.validate_timestamps=1
opcache.revalidate_freq=0
```

更多的配置指令可以看这里：    [http://php.net/manual/zh/opcache.configuration.php][1]


## 常用函数

zend opcache使用很简单，因为它启动后会自动运行．zend opcache会自动在内存中缓存预先编译好的php字节码，如果缓存了某个文件的字节码，就执行对应的字节码．常见的关于zend opcache扩展的函数：

```
opcache_compile_file($php_file); #预生成opcode缓存

opcache_is_script_cached($php_file) #查看是否生成opcode缓存

opcache_invalidate($php_file, true) #清除单个缓存

opcache_reset(); #清空缓存

opcache_get_status(); #获取缓存的状态信息

opcache_get_configuration(); #获取缓存的配置信息
```

以上


[0]: https://windows.php.net/downloads/pecl/releases/opcache/
[1]: http://php.net/manual/zh/opcache.configuration.php