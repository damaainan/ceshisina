## Sublime Text3下SublimeCodeIntel的使用

作者: Don 时间: June 30, 2016 分类: 代码分析

SublimeCodeIntel是sublime text下的一款代码提示插件。

安装SublimeCodeIntel

在Sublime Text3下同时按住`ctrl+shift+p`，然后输入`install`，选择`Install Package`。

![Install Package](http://ww2.sinaimg.cn/large/63c9befagw1f59rzbqew9j20ch03it91.jpg)

然后输入`SublimeC`，选择`SublimeCodeIntel`进行安装。

![SublimeCodeIntel](http://ww4.sinaimg.cn/large/63c9befagw1f5d37rot1cj20c50bgwh0.jpg)

安装完成后，会看到如下的说明。

![SublimeCodeIntel安装](http://ww4.sinaimg.cn/large/63c9befagw1f5d389avh3j20x40mj7ct.jpg)
配置SublimeCodeIntel

打开`SublimeCodeIntel`的配置文件，依次点击`Preferences->Package Settings->SublimeCodeIntel->Settings - User`。

安装完成后，默认`User`的配置文件为空，需要从`Default`下拷贝过来。`Default`配置文件，依次点击`Preferences->Package Settings->SublimeCodeIntel->Settings - Default`。

搜索`PHP`，找到形如下面的代码。
```
"PHP": {
    "php": "/Applications/MAMP/bin/php/php5.5.3/bin/php",
    "codeintel_scan_extra_dir": [],
    "codeintel_scan_files_in_project": true,
    "codeintel_max_recursive_dir_depth": 15,
    "codeintel_scan_exclude_dir":["/Applications/MAMP/bin/php/php5.5.3/"]
}
```
修改`php`后面的值为你的PHP路径，windows下的话需要使用`/`替换`\`。
修改`codeintel_scan_extra_dir`后面的值为你的项目路径，代码提示、跳转的索引会在这个路径下去建立。

下面是我修改后的配置。
```
"PHP": {
    "php": "C:/xampp/php/php.exe",
    "codeintel_scan_extra_dir": ["E:/svn/image/trunk"],
    "codeintel_scan_files_in_project": true,
    "codeintel_max_recursive_dir_depth": 15,
    "codeintel_scan_exclude_dir":["C:/xampp/php"]
}
```
修改完后，重启Sublime Text程序，它会在后台创建索引。这个时候你按住`alt`，点击某个函数，会看到下面这个提示。

![建立索引](http://ww3.sinaimg.cn/large/63c9befagw1f5d3mfhq5oj20gu011glz.jpg)
尽情使用吧

按住`alt`，点击某个函数会跳转到具体方法。
写代码的时候，会提示某个类下的所有方法。

![SublimeCodeIntel提示](http://ww4.sinaimg.cn/large/63c9befagw1f5d3pf0b8mj20l30b140r.jpg)
我个人感觉sublime text自带的代码提示就够了。

## `.codeintel` 目录

设置 **`codeintel_database_dir`** 目录，否则会在 C 盘生成 `.codeintel` 目录