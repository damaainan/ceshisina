## 利用PHP扩展Taint找出网站的潜在安全漏洞实践

来源：[https://segmentfault.com/a/1190000016032501](https://segmentfault.com/a/1190000016032501)


## 一、背景

笔者从接触计算机后就对网络安全一直比较感兴趣，在做PHP开发后对WEB安全一直比较关注，2016时无意中发现Taint这个扩展，体验之后发现确实好用；不过当时在查询相关资料时候发现关注此扩展的人数并不多；最近因为换了台电脑，需要再次安装了此扩展，发现这个扩展用的人还是比较少，于是笔者将安装的过程与测试结果记录下来，方便后续使用同时也让更多开发者来了解taint

taint扩展作者惠新宸曾经在自己的博客上有相关介绍，参考文档：[PHP Taint – 一个用来检测XSS/SQL/Shell注入漏洞的扩展][7]

## 二、操作概要


* 源码下载与编译
* 扩展配置与安装
* 功能检验与测试


## 三、源码下载与编译

Taint扩展PHP本身并不携带，在linux或mac系统当中笔者需要下载源码自己去编译安装
### 3.1 源码下载

笔者的开发环境是mac系统，所以需要去PHP的pecl扩展网站去下载源码，其中taint的地址为：

```
https://pecl.php.net/package/taint
```

在扩展网址的的尾部，可以看到有一排下载地址，如下图

![][0]

笔者需要选择一个自己合适的版本，笔者的开发环境使用的是PHP7.1，因此选择了最新的版本，对应下载地址如下：

```
https://pecl.php.net/get/taint-2.0.4.tgz
```

使用wget下载该源码,参考命令如下：

```
wget https://pecl.php.net/get/taint-2.0.4.tgz
```

下载下来之后，笔者需要解压，解压命令参考如下：

```
tar -zxvf taint-2.0.4.tgz
```

解压之后，进入目录,参考命令如下：

```
cd taint-2.0.4
```
### 3.2 源码编译

现在笔者需要编译一下源码，在编译之前可以使用phpze来探测PHP的环境，参考命令如下：

```
phpize
```

返回结果如下

```
Configuring for:
PHP Api Version:         20160303
Zend Module Api No:      20160303
Zend Extension Api No:   320160303
```

生成 Makefile，为下一步的编译做准备

```
./configure 
```

返回结果

```
checking how to hardcode library paths into programs... immediate
checking whether stripping libraries is possible... yes
checking if libtool supports shared libraries... yes
checking whether to build shared libraries... yes
checking whether to build static libraries... no

creating libtool
appending configuration tag "CXX" to libtool
configure: creating ./config.status
config.status: creating config.h
```

开始编译,并安装

```
make && make install
```

```
(cd .libs && rm -f taint.la && ln -s ../taint.la taint.la)
/bin/sh /Users/song/taint-2.0.4/libtool --mode=install cp ./taint.la /Users/song/taint-2.0.4/modules
cp ./.libs/taint.so /Users/song/taint-2.0.4/modules/taint.so
cp ./.libs/taint.lai /Users/song/taint-2.0.4/modules/taint.la
----------------------------------------------------------------------
Libraries have been installed in:
   /Users/song/taint-2.0.4/modules

If you ever happen to want to link against installed libraries
in a given directory, LIBDIR, you must either use libtool, and
specify the full pathname of the library, or use the `-LLIBDIR'
flag during linking and do at least one of the following:
   - add LIBDIR to the `DYLD_LIBRARY_PATH' environment variable
     during execution

See any operating system documentation about shared libraries for
more information, such as the ld(1) and ld.so(8) manual pages.
----------------------------------------------------------------------

Build complete.
Don't forget to run 'make test'.

Installing shared extensions:     /usr/local/Cellar/php71/7.1.14_25/lib/php/extensions/no-debug-non-zts-20160303/
```
## 四、配置与安装

在编译扩展之后，笔者还需要把Taint放到指定位置，以及修改配置文件让其生效
### 4.1 配置taint

笔者首先需要知道PHP的配置文件是多少，然后通过查看配置文件的扩展路径，才能把so文件放到对应里面去，查看配置文件位置命令如下：

```
php --ini
```

返回结果如下

```
Configuration File (php.ini) Path: /usr/local/etc/php/7.1
Loaded Configuration File:         /usr/local/etc/php/7.1/php.ini
Scan for additional .ini files in: /usr/local/etc/php/7.1/conf.d
Additional .ini files parsed:      /usr/local/etc/php/7.1/conf.d/ext-opcache.ini
```

笔者可以看到php.ini放置在`/usr/local/etc/php/7.1/php.ini`当中

知道配置文件之后，笔者需要找到扩展文件夹位置，参考命令如下

```
cat /usr/local/etc/php/7.1/php.ini | grep extension_dir
```

命令执行结果如下，笔者可以看出扩展文件夹位置是`/usr/local/lib/php/pecl/20160303`

```
extension_dir = "/usr/local/lib/php/pecl/20160303"
; extension_dir = "ext"
; Be sure to appropriately set the extension_dir directive.
;sqlite3.extension_dir =
```
### 4.2 安装扩展

现在笔者需要把扩展文件复制到，PHP的扩展文件位置，参考命令如下

```
cp /usr/local/Cellar/php71/7.1.14_25/lib/php/extensions/no-debug-non-zts-20160303/taint.so /usr/local/lib/php/pecl/20160303/
```

复制完成之后，笔者需要编辑配置文件，将taint的配置项复制进去

```
vim /usr/local/etc/php/7.1/php.ini
```

增加Tain的配置项到php.ini文件当中，参考配置如下：

```
[taint]
extension=taint.so
taint.enable=1
taint.error_level=E_WARNING
```
### 4.3 安装结果验证

保存配置文件并退出之后，则代表笔者的安装已经完成，现在需要重启一下php让其生效，参考命令如下

```
brew services restart php@7.1
```

重启完成之后，可以通过命令查看PHP当前的扩展有没有Taint，参考命令如下:

```
php -i | grep taint
```

返回结果如果出现了一下信息，基本上已经安装成功。

```
taint
taint support => enabled
taint.enable => On => On
taint.error_level => 2 => 2
```
## 五、功能检验与测试

完成上面的两步操作之后，笔者安装阶段已经大功告成了，现在笔者需要用taint来检验效果，检验分为三部分，首先用taint作者的demo代码进行检验，之后用渗透测试系统permeate来检验，最后以笔者平时所开发的代码进行测试。
### 5.1 demo文件测试

用demo文件测试的目的是检验笔者安装的taint是否真的已经生效，并确认taint有没有意义。
#### 5.1.1 复制demo代码

在作者的GitHub上面有下面的这样一份demo代码，笔者将其复制到web目录，位置如下：

```
/Users/song/mycode/safe/permeate
```

demo代码内容如下，读者实验时可以将其拷贝：

```php
<?php
$a = trim($_GET['a']);

$file_name = '/tmp' .  $a;
$output    = "Welcome, {$a} !!!";
$var       = "output";
$sql       = "Select *  from " . $a;
$sql      .= "ooxx";

echo $output;

print $$var;

include($file_name);

mysql_query($sql);
```
#### 5.1.2 配置虚拟主机

当代码文件保存之后，笔者需要在nginx配置文件中增加一个虚拟主机，用于浏览器访问此文件，参考配置如下：

```nginx
    server {
        listen       80;
        server_name  test.localhost;
        root  /Users/song/mycode/safe/permeate;
        location / {
            index index.html index.htm index.php; 
        }

        location ~ \.php$ {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }
    }
```
#### 5.1.3 浏览器访问

接着笔者通过浏览器访问对应代码文件，URL地址如下：

```
http://test.localhost/taintdemo.php?a=1
```

浏览器访问页面之后，笔者能在页面中看到一些警告信息，内容如下：

```
Warning: main() [echo]: Attempt to echo a string that might be tainted in /Users/song/mycode/work/test/taintdemo.php on line 10
Welcome, 1 !!!
Warning: main() [print]: Attempt to print a string that might be tainted in /Users/song/mycode/work/test/taintdemo.php on line 12
Welcome, 1 !!!
Warning: main() [include]: File path contains data that might be tainted in /Users/song/mycode/work/test/taintdemo.php on line 14

Warning: include(/tmp1): failed to open stream: No such file or directory in /Users/song/mycode/work/test/taintdemo.php on line 14

Warning: include(): Failed opening '/tmp1' for inclusion (include_path='.:/usr/local/Cellar/php@7.1/7.1.19/share/php@7.1/pear') in /Users/song/mycode/work/test/taintdemo.php on line 14

Fatal error: Uncaught Error: Call to undefined function mysql_query() in /Users/song/mycode/work/test/taintdemo.php:16 Stack trace: #0 {main} thrown in /Users/song/mycode/work/test/taintdemo.php on line 16
```

从警告信息当中可以看出，笔者的taint已经生效，给出了很多警告提示，提示参数可能受到污染，因为参数并没有经过任何过滤；
#### 5.1.4 参数过滤测试

如果不想让taint给出警告提示，可以将demo代码中的第二行代码更改或增加一下过滤规则，参考代码如下：

```php
$a = htmlspecialchars($_GET['a']);
```

再次回到浏览器当中，刷新当前页面，可以看到返回的信息已经发生了变化，返回内容如下

```
Welcome, 1 !!!Welcome, 1 !!!
Warning: include(/tmp1): failed to open stream: No such file or directory in /Users/song/mycode/work/test/taintdemo.php on line 15

Warning: include(): Failed opening '/tmp1' for inclusion (include_path='.:/usr/local/Cellar/php@7.1/7.1.19/share/php@7.1/pear') in /Users/song/mycode/work/test/taintdemo.php on line 15

Fatal error: Uncaught Error: Call to undefined function mysql_query() in /Users/song/mycode/work/test/taintdemo.php:17 Stack trace: #0 {main} thrown in /Users/song/mycode/work/test/taintdemo.php on line 17
```

因为笔者在代码中增加了参数转义，此时再次刷新浏览器，会看到taint不再给发出警告提醒。
### 5.2 渗透测试系统验证

用demo系统验证taint扩展生效之后，现在笔者将用一个渗透测试系统来做一个实验，在这个系统中本身存在了很多安全问题，使用taint来找出这些问题，使用的渗透测试系统为`permeate渗透测试系统`,地址如下

笔者之前有写过一篇文章介绍此系统，参考文档：[WEB安全Permeate漏洞靶场挖掘实践][8]

```
https://git.oschina.net/songboy/permeate
```
#### 5.2.1 下载permeate

笔者通过git将其源码下载下来，参考命令如下

```
https://gitee.com/songboy/permeate.git
```

下载下来之后，同样创建一个虚拟主机，可以参考上面的nginx配置
#### 5.2.2 导入数据库

因为这个系统会用到数据库，所以笔者下载之后需要新建数据库给permeate使用

![][1]

新建完成数据库之后，笔者需要将一些数据表结构以及初始化数据导入到数据库当中，在使用git下载下来之后，在其跟目录有一个doc的文件夹，笔者打开它之后，能看到有一个sql文件，如下图所示

![][2]

打开此文件并将其里面的内容复制，将复制的内容到管理数据库的Navicat Premium当中，然后执行这些SQL语句，如下图所示


![][3]
#### 5.2.3 修改配置文件

导入数据库完成之后，笔者修改数据库配置文件，让permeate能够连接次数据库，配置文件在根目录`conf/dbconfig.php`,里面的配置代码如下，将其地址账户以及密码和数据库名称一一对应填写

```php
<?php
    !defined('DB_HOST') && define('DB_HOST','127.0.0.1');
    !defined('DB_USER') && define('DB_USER','root');
    !defined('DB_PASS') && define('DB_PASS','root');
    !defined('DB_NAME') && define('DB_NAME','permeate');
    !defined('DB_CHARSET') && define('DB_CHARSET','utf8');
    $sex=array('保密','男','女');
    $edu=array('保密','小学','初中','高中/中专','大专','本科','研究生','博士','博士后');
    $admins=array('普通用户','管理员')

```
#### 5.2.4 验证安装结果

设置好数据库之后，笔者安装permeate便已经完成了，此时打开首页，看到的界面应该如下图所示：


![][4] 
如果在首页当中没有看到板块以及分区等信息，很有可能是数据库没有连接成功或者数据库没有正确导入数据所致。
#### 5.2.5 挖掘漏洞

下面开始进行测试，笔者点击第一个板块`SQL注入`，并点击列表下发的`下一页`按钮，此时看到的页面如下图所示：


![][5] 
在这个板块列表页中没看到任何问题，但是实际上taint已经给笔者发出了警告提醒。

笔者可以通过查看源代码时候来看到这些问题，如下图所示，taint提示在代码文件`/Users/song/mycode/safe/permeate/core/common.php`的50行，存在参数被污染的情况。

![][6]
#### 5.2.5 漏洞分析

笔者找到对应的代码位置，发现代码内容如下：

```php
function includeAction($model, $action)
{
    //判断控制器是否存在
    $filePath = "./action/$model.php";
    if (is_readable($filePath)) {
        require_once $filePath;
        $class = new $model;
        if (is_callable(array($class, $action))) {
            $class->$action();
            return true;
        }
    }

```

在代码中笔者看到有一个`require_once`函数加载了文件，里面的参数使用了变量`$model`和`$action`,通过最终变量来源，在代码文件`/Users/song/mycode/safe/permeate/home/router.php`发现这两个参数确实没有经过过滤，如下代码所示:

```php
<?php
require_once "/core/common.php";
$model = !empty($_GET['m']) ? $_GET['m'] : 'index';
$action = !empty($_GET['a']) ? $_GET['a'] : 'index';

includeAction("$model","$action");
```

最后需要提醒大家，Taint在开发环境安装即可，不要安装到生产环境当中，否则可能会把网站的安全问题直接暴露给攻击者。


-----

作者：汤青松

日期：2018年08月16日

微信：songboy8888

[7]: http://www.laruence.com/2012/02/14/2544.html
[8]: https://segmentfault.com/a/1190000016027438
[0]: ../img/1460000016032504.png
[1]: ../img/1460000016032505.png
[2]: ../img/1460000016032506.png
[3]: ../img/1460000016032507.png
[4]: ../img/1460000016032508.png
[5]: ../img/1460000016032509.png
[6]: ../img/1460000016032510.png