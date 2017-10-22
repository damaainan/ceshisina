# 使用 xunsearch 构建全文搜索功能

 时间 2015-12-23 06:14:01  灵感 - 来自生活的馈赠

原文[https://www.insp.top/article/use-xunsearch-to-build-fulltext-search-function][1]


很多人希望自己的站点拥有一个强大的搜索功能，用于检索自己站点上的内容，以便于用户访问时快速寻找有用信息。一般的方式是利用 SQL 的 LIKE 语句。但是这样的检索命中率底下且效率不高，其次是程序逻辑实现更为复杂，对于简单的搜索勉强行得通，如果想实现更为高级的效果，就需要使用 全文搜索引擎 。 

大家肯定听说过 Sphinx 、 Elastic search 、 Xunsearch（讯搜） 或者其他全文搜索引擎。由于讯搜天然支持中文分词，也就成为了我的第一选择。本篇博客内容仅针对大致的架设方法讲解，作为国内开发的全文搜索引擎，文档也很清晰，建议有更多需求的去阅读官方网站下的 SDK 文档： [xunsearch 官方网站][4]

## 安装

我是在 CentOS 环境下安装的，编译安装方式十分简单，当然前提是系统中已经安装好了 make 和 gcc。

## 下载源码解压

    wget http://www.xunsearch.com/download/xunsearch-full-latest.tar.bz2
    tar -xjf xunsearch-full-latest.tar.bz2

## 执行安装脚本

xunsearch 的安装直接执行安装脚本即可，会自动安装，在安装过程中会提示安装目录等。一般默认即可，默认是 **_/usr/local/xunsearch_** 。 

    cd xunsearch-full-1.4.9
    sh setup.sh

## 启动 xunsearch

启动 xunsearch 很简单，直接进入刚刚 xunsearch 的安装目录下的 bin 目录，如 /usr/local/xunsearch/bin。然后执行命令 sh xs-ctl.sh start 或 sh xs-ctl.sh restart 。 

默认的， xunsearch 全文搜索引擎会监听 8383，8384 两个端口，对于一些情况下可以通过 iptables 对两个端口设置访问权限。

## 在项目中使用

无论什么项目中，我们最终都是通过 xunsearch 提供的 SDK 中的组件实现具体功能。Xunsearch 的 SDK 简单易用，本文主要针对几点基本的过程讲述。提供的 SDK 中的组件都是完全面向对象的，默认情况下安装 xunsearch 时已经包含了 SDK。但是为了快速使用，我依旧建议通过 Composer 将 SDK 安装至您的项目中，尤其是对于 Laravel、Symfony、Yii Framework 这类本身就依赖 Composer 的框架。

## Composer 安装 SDK

1.通过命令直接引入

composer require --prefer-dist hightman/xunsearch "*@beta"

2.通过在 composer.json 引入

"hightman/xunsearch": "*@beta"

## 创建配置文件

配置文件是项目索引的灵魂，xunsearch 会根据一个配置文件生成、创建、修改、查询一个索引，在你的项目中，当需要进行全文检索，都需要指定一个具体的配置文件，根据这个配置文件，xunsearch 才会知道你想要查询哪一个库。

对于全文搜索引擎而言，索引就好比 MySQL 的一个具体的数据库（或数据表），配置文件就是定义这个数据表的名称、字段、字段类型的文件。

一个项目可以有多个配置文件，因为存在不同的索引需求，我们通常应当把一系列配置文件保存在目录下，这个目录就是配置文件的根目录。

在项目代码的起始，通过定义一个常量 **_XS_APP_ROOT_** ，这个值就是那些配置文件保存的目录，当我们实例化一个 xunsearch 实例时，通过传入配置文件名来告诉 xunsearch 我们操作的是哪一个索引。 

关于配置文件如何定义参考官方文档： [项目配置文件详解][5]

## 索引的增删改查

我们所有的操作基本上围绕着一个类： **_XS_** 。 

    define('XS_APP_ROOT', __DIR__ . '/search');
    
    // 实例化 XS 类
    // 参数是我们之前讲的配置文件名（去掉 .ini 扩展名后的名称）
    // xunsearch 会自动在我们前面定义的 XS_APP_ROOT 下寻找 project.ini
    $xs = new XS('project');

通过创建 xunsearch 实例，我们后续的操作基本围绕着这个实例展开，包括增删改查数据、处理查询结果等等。

### 增删改

    // 获取 XSIndex 对象，该对象负责对索引进行增删改查操作
    $index = $xs->index;
    
    // 增加文档
    // 首先创建一个 XSDocument 对象。索引中每一条记录称为一个 Document
    $document = new XSDocument;
    $document->setFields([
        'id' => 1,
        'title' => 'Article title',
        'content' => 'Article content ............'
    ]);
    $index->add($document);
    
    // 更改文档
    // 在 Xunsearch PHP-SDK 中，更新、修改文档和添加文档的做法非常的类似
    // 只不过调用的是 XSIndex::update，并且在内部处理上有所区别。
    // 修改文档依旧需要填入每一项字段
    $document = new XSDocument;
    $document->setFields([
        'id' => 1,
        'title' => 'New Article title',
        'content' => 'New Article content ............'
    ]);
    $index->update($document);
    
    // 删除文档
    $index->del(1);

上述内容基本罗列了普遍使用的数据的增删改的方式，为什么没有说查呢？因为“查”是比较特殊的。我们马上开始说。

### 查

在 PHP-SDK 中，搜索功能由类型为 XSSearch 的对象所维护。在 XS 项目中，通过读取 XS::search 属性来获取搜索操作对象，然后展开使用，而不是自行创建对象。

查询的方式也十分简单：

    $search = $xs->search;
    
    $search->setQuery('什么是容器'); // 设置搜索语句，xunsearch 会自动分词
    $search->addWeight('title', '容器'); // 增加附加条件：提升标题中包含 '容器' 的记录的权重
    $search->setLimit(5, 10); // 设置返回结果最多为 5 条，并跳过前 10 条
    
    $docs = $search->search(); // 执行搜索，将搜索结果文档保存在 $docs 数组中
    $count = $search->count(); // 获取搜索结果的匹配总数估算值
    
    // 遍历输出查询结果
    foreach ($docs as $doc)
    {
        $title = $search->highlight($doc->title); // 高亮处理 title 字段
        $content = $search->highlight($doc->content); // 高亮处理 message 字段
    
        printf("[%s]\n%s\n\n", $title, $content);
    }

实际上，查询的条件、结果筛选的方式和处理细节还有很多方法，官方文档已经给出了十分详细的讲解，在此不再赘述。希望通过这篇不算很精致的文章让大家大概了解全文搜索其实并不复杂 ~~ 你也可以很快实现一个更高级的哟 ~


[1]: https://www.insp.top/article/use-xunsearch-to-build-fulltext-search-function

[4]: http://www.xunsearch.com/
[5]: http://www.xunsearch.com/doc/php/guide/ini.guide