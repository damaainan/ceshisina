## 讯搜（xunsearch）的初步学习

<font face=微软雅黑>

XunSearch是一款很不错的中文全文检索工具使用xunsearch快速构建自己的PHP全文搜索项目。需要注意的是XunSearch只能在Linux和Unix下运行。官方发布了一个DEMO 直接下载 PHP-SDK 就可以开发测试。

![未标题-1.jpg][0]

### 安装服务端

##### 1、安装说明：请先安装好所有的工具，比如：gcc 、gcc-c++ 、make 等。安装目录推荐使用 $HOME/xunsearch 或 /usr/local/xunsearch ( 以下简称 $prefix)。
##### 2、安装过程：

    wget http://www.xunsearch.com/download/xunsearch-full-latest.tar.bz2
    tar -xjf xunsearch-full-latest.tar.bz2
    cd xunsearch-full-1.4.10/
    sh ./setup.sh

剩下的就直接回车，安装到默认目录下即可。
##### 3、安装完毕后，您就可以通过自带的脚本 `($prefix/bin/xs-ctl.sh)` 启动/关闭 `xunsearch` 服务端了。用法举例：

    $prefix/bin/xs-ctl.sh start          # 默认启动，绑定本地的 8383/8384  端口
    $prefix/bin/xs-ctl.sh stop          # 停止服务器，若启动时指定了 -b inet 此处也必须指定

##### 4. 没错，安装就是这么简单。
特别提示，搜索的所有索引数据将被保存到 `$prefix/data` 目录，因此如果您希望数据目录另行安排，请采用软连接形式确保 `$prefix/data` 链至真实数据目录。此外，如果服务端启动时使用了 `-b inet 参数`，那么请借助 iptables 或其它防火墙工具进行保护，`xunsearch` 本身出于性能考虑不做其它验证处理。

##### 5、我自己在第一次安装的时候，遇到了一个启动不了的问题。

请查看[http://www.yduba.com/biancheng-4631516355.html][1]，主要是我的根目录磁盘满了。

### PHP-SDK说明

PHP-SDK 的代码默认包含在服务端安装目录中，即 `$prefix/sdk/php`， 目录结构如下：

    |–  doc/                                         — HTML  格式的文档、API手册
        |–  app/                                    —  搜索项目 ini  文件的默认存储目录
        |–  lib/XS.php                              —  搜索库唯一文件，所有搜索相关功能均必须引入此文件
        |-  util/                                 —  辅助工具目录
                |–  RequireCheck.php                —  检测您的 PHP  环境是否符合 xunsearch  运行条件
                |–  Quest.php                       —  搜索测试工具
                |-  Indexer.php                      —  索引管理工具

特别说明一下： `doc` 目录是帮助手册，我们直接下载到本地，然后把服务器上的删除即可。`util` 是一个命令行的操作工具 比如： `php ./util/Quest.php demo abc` 会去搜索 `abc`，一般情况下，不会直接到服务器上做操作。所以，这个目录也是可以下载到本地之后，把服务器上的删除的。我们在自己的代码中如果有问题，可以直接到这个工具库里查看这些代码是怎么实现的。服务器上的，只要 `app` 和 `lib/XS.php` 这两个目录即可。

### 开发流程

• 为便于讲解说明，假定 `PHP-SDK` 代码目录为 `$sdk` 。

• 分析搜索需求，设计搜索应用必需的字段。

• 编写项目配置文件，项目配置 `ini` 文件存放在 `$sdk/app` 目录。

• 引入 `$sdk/lib/XS.php` 进行搜索功能和界面开发;

剩下的工作就是根据自己的需要写自己的代码了。这里先不再介绍更多的用法，以后再慢慢介绍。（因为我也是一个初学者），您可以去官方去查看他们的帮助手册（先学明一下，个人感觉他们的文档写的好乱的，一下子很难看清头绪）

我这里先简单的写一个Demo，可以给初学者一个直接的参考。我的 `Demo` 目录结构如下，`app/demo.ini` 和 `lib/XS.php` 是从 `PHP-SDK` 中复制过来的

    |- www/
         |–  index.php          — 创建索引
         |-   search.php            — 搜索文件
         |–  app/demo.ini           —  搜索项目 ini
         |–  lib/XS.php             —  搜索库唯一文件

**第一步：** 要先配置搜索项目文件，我们在 `/www/app/` 下创建一个 `demo.ini` 文件，内容如下：

```
    project.name = demo
    project.default_charset = utf-8
    server.index    = 8383
    server.search   = 8384
    
    [que_id]
    type = id
    
    [que_gid]
    type = string
    
    [que_info]
    type = title
```

说明一下: `project.name` 就是项目配置名，和文件名一样就可以了。 `server.index` 和 `server.search` 是索引服务和搜索服务的地址和端口，如果项目代码和服务是同一个机器，就只要写端口就可以了。`que_id`、`que_gid`、`que_info` 是自己的字段，一般情况下和 mysql 数据库中的表一致就可以，不一致也没有关系。 `type = id` 说明 `que_id` 是一个主键，以后删除文档、更新文档都要根据此ID

**第二步：** 创建自己的索引文件（这个就是用于添加索引的，真正的项目里是不需要这样做的，这只是 Demo 哦），代码如下：

```php
    require '/www/lib/XS.php';
    $xs     = new \XS('demo');
    $index  = $xs->index;
    $doc    = new \XSDocument;
    
    $doc->setFields( array('que_id'=>1, 'que_gid'=>1, 'que_info'=>'这是一个新问题') );
    $doc->setFields( array('que_id'=>2, 'que_gid'=>2, 'que_info'=>'哈哈，这是一个测试') );
    $doc->setFields( array('que_id'=>3, 'que_gid'=>2, 'que_info'=>'唉，是不是应该这样呢') );
    $doc->setFields( array('que_id'=>4, 'que_gid'=>2, 'que_info'=>'欢迎访问易读小屋') );
    $doc->setFields( array('que_id'=>5, 'que_gid'=>1, 'que_info'=>'易读小屋以后会添加更多的好文章哦') );
    
    $index->flushIndex();
```

我们向 demo （就是第一步创建的 `demo.ini`）项目里添加了5个文档。

**第三步：** 创建自己的搜索文件（这个是最主要的，因为用户是通过这个地址来搜索内容的）,代码如下：

```php
    require '/www/lib/XS.php';
    
    $keywords = "易读小屋"; // 这是用户输入的搜索词
    
    $xs     = new \XS('demo');
    $xs->defaultCharset = "UTF-8";
    
    // setFuzzy 表示是不是，开启模糊搜索
    $doc = $xs->search->setFuzzy(true)->setQuery( $keywords )->search();
    
    print_r( $doc ); // 这是搜索出来的内容
```

</font>

[0]: ../img/1482141697345244.jpg
[1]: http://www.yduba.com/biancheng-4631516355.html


