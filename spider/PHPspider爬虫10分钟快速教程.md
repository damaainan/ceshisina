## PHPspider爬虫10分钟快速教程

来源：[http://www.jianshu.com/p/01052508ea7c](http://www.jianshu.com/p/01052508ea7c)

时间 2018-04-15 07:08:53

 
说到做爬虫，大家都可能第一时间想到的是python，其实php也是可以用来写爬虫程序的。php一贯简洁、易用，亲测使用PHPspider框架10分钟就能写出一个简单的爬虫程序。
 
### 一、PHP环境安装
 
和python一样，PHP也需要环境，可以使用官网下载的PHP，也可以使用XAMPP、PHPstudy等集成环境下的PHP。比较推荐集成环境，省去单独安装Mysql数据库。
 
### 二、composer安装
 
composer是PHP下的依赖包管理工具，类似于Python中的PIP。
 
中文官网为 [https://www.phpcomposer.com/][7]
 
下载安装即可，win+R运行cmd，输入composer命令，出现如下图所示说明安装成功了。
 
  
  
![][0]
 
 
 
### 三、PHPspider安装
 
在任意位置建立一个文件夹，例如我们要抓取简书的数据，我们可以在D盘建立jianshu文件夹，然后cmd命令进入该文件夹，运行命令
 
``` 
composer require owner888/phpspider
```
 
如下结果便是成功安装了。
 
  
  
![][1]
 
 
 
### 四、开始写第一个爬虫
 
现在打开jianshu文件夹，会发现里面多了一些东西，不用管它，建立一个php文件，开始打代码。
 
  
  
![][2]
 
 
 
开发文档在这： [https://doc.phpspider.org/demo-start.html][8]
 
这边不讲基础，直接上代码，因为咱们是做的10分钟快速教程。
 
匹配方式使用XPach语法。
 
```php
<?php
require '/vendor/autoload.php';
use phpspider\core\phpspider;

/* Do NOT delete this comment */
/* 不要删除这段注释 */

$configs = array(
'name' => '简书',
'log_show' =>false,
'tasknum' => 1,
//数据库配置
'db_config' => array(
'host'  => '127.0.0.1',
'port'  => 3306,
'user'  => 'root',
'pass'  => '',
'name'  => 'demo',
),
'export' => array(
'type' => 'db',
'table' => 'jianshu',  // 如果数据表没有数据新增请检查表结构和字段名是否匹配
),
//爬取的域名列表  
'domains' => array(
    'jianshu',
    'www.jianshu.com'
), 
//抓取的起点
'scan_urls' => array(
    'https://www.jianshu.com/c/V2CqjW?utm_medium=index-collections&utm_source=desktop'
),
//列表页实例
'list_url_regexes' => array(
    "https://www.jianshu.com/c/\d+"
),
//内容页实例
//  \d+  指的是变量
'content_url_regexes' => array(
    "https://www.jianshu.com/p/\d+",
),
'max_try' => 5,

'fields' => array(
    array(
        'name' => "title",
        'selector' => "//h1[@class='title']",
        'required' => true,
    ),
    array(
        'name' => "content",
        'selector' => "//div[@class='show-content-free']",
        'required' => true,
    ),
),
);

$spider = new phpspider($configs);
$spider->start();
```
 
稍微解释一下一下句法的含义：
 
//h1[@class='title']
 
获取所有class值为title的h1节点
 
//div[@class='show-content-free']
 
获取所有class值为show-content-free的div节点
 
具体为什么这么写呢？自己看简书的html源码吧。
 
打完代码后，记得根据要抓取的内容建立对应的数据库、数据表，字段要能对对上。
 
  
  
![][3]
 
 
 
接着cmd，输入
 
```php
php -f d:\jianshu\spider.php

```
 
运行如下
 
  
  
![][4]
 
 
 
  
  
![][5]
 
 
 
打开数据看一下，是不是都抓取到了呢？
 
  
  
![][6]
 
 
 


[7]: https://link.jianshu.com?t=https%3A%2F%2Fwww.phpcomposer.com%2F
[8]: https://link.jianshu.com?t=https%3A%2F%2Fdoc.phpspider.org%2Fdemo-start.html
[0]: https://img2.tuicool.com/3yAfQji.png 
[1]: https://img2.tuicool.com/yQvuMzJ.png 
[2]: https://img2.tuicool.com/qAriqm2.png 
[3]: https://img0.tuicool.com/J32qumr.png 
[4]: https://img1.tuicool.com/6bamuu2.png 
[5]: https://img0.tuicool.com/RvMbEvA.png 
[6]: https://img1.tuicool.com/rErQ3ur.png 