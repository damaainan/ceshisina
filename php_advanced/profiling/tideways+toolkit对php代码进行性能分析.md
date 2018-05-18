## tideways+toolkit对php代码进行性能分析

来源：[https://segmentfault.com/a/1190000014806385](https://segmentfault.com/a/1190000014806385)

toolkit是tideway官方提供的性能分析的命令行工具。如果你只是本地开发调试接口性能，不想安装xhgui，那么使用toolkit就足够了
## 安装
### 安装tideways拓展

``` 
git clone https://github.com/tideways/php-xhprof-extension.git
cd php-profiler-extension
phpize
./configure
make && make install
```

在php.ini中加入

``` 
extension=tideways_xhprof.so
```

重启php-fpm

``` 
service php-fpm restart
```
### toolkit安装

``` 
go get github.com/tideways/toolkit
# 安装graphviz
# macOS
brew install graphviz
# ubuntu
sudo apt-get install -y graphviz
```
### 设置别名

``` 
alias tk=toolkit
```
## tideways+toolkit
### 代码埋点

在程序入口中加入

```php
if (extension_loaded('tideways_xhprof')) {
    tideways_xhprof_enable(TIDEWAYS_XHPROF_FLAGS_CPU | TIDEWAYS_XHPROF_FLAGS_MEMORY);
}

// 你的代码
application();

if (extension_loaded('tideways_xhprof')) {
    $data = tideways_xhprof_disable();
    file_put_contents(
        sprintf('%s/app.xhprof', '/path/to'),
        json_encode($data)
    );
}
```

执行下代码，然后就会生成/path/to/app.xphrof
### 性能分析

``` 
tk analyze-xhprof /path/to/app.xphrof
```

![][0]

默认性能分析的指标是wt_excl，其他的指标有


* wt 调用时长，包括子函数
* excl_wt 调用时长，不包括子函数
* cpu CPU调用时长，包括子函数
* excl_cpu CPU调用时长，不包括子函数
* memory 内存消耗（字节），包括子函数
* excl_memory 内存消耗（字节），不包括子函数
* io io时长，包括子函数
* excl_io io时长，不包括子函数


### 生成性能瓶颈图

``` 
tk generate-xhprof-graphviz /path/to/app.xhprof
dot -Tpng callgraph.dot > callgraph.png
```

![][1]

显示的指标有


* 函数名
* Inc 函数运行时间，包括子函数
* Excl 函数运行时间，不包括子函数
* total calls 总调用次数


[0]: ../img/bVbahYA.png 
[1]: ../img/bVbahXn.png 