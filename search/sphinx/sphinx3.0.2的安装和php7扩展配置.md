## sphinx3.0.2的安装和php7扩展配置

来源：[http://www.cnblogs.com/myvic/p/8671891.html](http://www.cnblogs.com/myvic/p/8671891.html)

时间 2018-03-31 16:19:00


1.下载sphinx

没想到sphinx3解压后即可；

```

wget http://sphinxsearch.com/files/sphinx-3.0.2-2592786-linux-amd64.tar.gz
grep -v "^#" sphinx.conf.dist | grep -v "^\s#" | grep -v "^$" >sphinx.conf
安装依赖包：yum -y install make gcc g++ gcc-c++ libtool autoconf automake imake mysql-devel libxml2-devel expat-devel
不安装有可能报错误呢？

```

```

sql_query_pre = SET SESSION query_cache_type = OFF # 还特定于MySQL源代码，在预查询中禁用查询缓存（仅用于索引器连接）非常有用，因为索引查询不会经常重新运行，而且缓存结果也没有意义。这可以通过以下方式实现：





sql_ranged_throttle



远程查询限制期，以毫秒为单位。可选，默认值为0（不节流）。适用于SQL数据源（mysql，pgsql，mssql）只。
索引器在数据库服务器上施加太多负载时，节流可能很有用。它会使索引器在每个范围查询步骤中以指定的毫秒数休眠一次。这种睡眠是无条件的，并且在提取查询之前执行。
在每个查询步骤之前，sql_ranged_throttle = 1000＃睡眠1秒
 

```

```

    ## N-Gram索引的分词技术
    ## N-Gram是指不按照词典，而是按照字长来分词，这个主要是针对非英文体系的一些语言来做的（中文、韩文、日文）
    ## 对coreseek来说，这两个配置项可以忽略。
    # ngram_len     = 1
    # ngram_chars       = U+3000..U+2FA1F

```

```

source documents
{
    type            = mysql
    sql_host        = localhost
    sql_user        = root
    sql_pass        =
    sql_db            = test
    sql_port        = 3306    # optional, default is 3306
    sql_query        = \
        SELECT id, group_id, title, content \
        FROM documents
    
    sql_query_pre         = SET NAMES utf8
    sql_query_pre         = SET SESSION query_cache_type=OFF
    sql_query_pre         = REPLACE INTO sph_counter SELECT 1,MAX(id) FROM documents
    sql_ranged_throttle    = 0
}
source src1throttled : documents
{
    sql_ranged_throttle    = 100
}
index documents
{
    source            = documents
    path            = /var/data/documents
    docinfo            = extern
    dict            = keywords
    mlock            = 0
    morphology        = none
    min_word_len        = 1
    
    ngram_len        = 1
    ngram_chars        = U+3000..U+2FA1F
    html_strip        = 1
}
indexer
{
    mem_limit        = 128M
}
searchd
{
    listen            = 9312
    listen            = 9306:mysql41
    log            = /var/log/searchd.log
    query_log        = /var/log/query.log
    read_timeout        = 5
    client_timeout        = 300
    max_children        = 30
    persistent_connections_limit    = 30
    pid_file        = /var/log/searchd.pid
    seamless_rotate        = 1
    preopen_indexes        = 1
    unlink_old        = 1
    mva_updates_pool    = 1M
    max_packet_size        = 8M
    max_filters        = 256
    max_filter_values    = 4096
    max_batch_queries    = 32
    workers            = threads # for RT to work
    binlog_path        = /var/data    
}

```

2.报错信息

执行：./searchd -c /usr/local/sphinx/etc/sphinx.conf

FATAL: failed to open '/usr/local/var/data/binlog.lock': 2 'No such file or directory

解决：配置 即可：找的位置不对： binlog_path = /var/data

2.2

执行：./indexer -c /usr/local/sphinx/etc/sphinx.conf --all

ERROR: index 'documents': sql_query_pre[1]: Query cache is disabled; restart the server with query_cache_type=1 to enable it (DSN=mysql://root:***@localhost:3306/test)

解决：mysql没有开启缓存

```

如果何配置查询缓存：

　　query_cache_type 这个系统变量控制着查询缓存工能的开启的关闭。

　　query_cache_type=0时表示关闭，1时表示打开，2表示只要select 中明确指定SQL_CACHE才缓存。

　　这个参数的设置有点奇怪，1、如果事先查询缓存是关闭的然而用 set @@global.query_cache_type=1; 会报错

　　ERROR 1651 (HY000): Query cache is disabled; restart the server with query_cache_type=1 to enable it

　　2、如果事先是打开着的尝试去闭关它，那么这个关闭也是不完全的，这种情况下查询还是会去尝试查找缓存。

　　最好的关闭查询缓存的办法就是把my.cnf 中的query_cache_type=0然后再重启mysql。

```

二.安装php扩展 --sphinx

我的环境是php7.官网上没有，但是在[[Browse Source][0]] 找到了

  
```

wget http://git.php.net/?p=pecl/search_engine/sphinx.git;a=snapshot;h=339e123acb0ce7beb2d9d4f9094d6f8bcf15fb54;sf=tgz

```

```

#竟然还下不来，只能ftp上传了
解压：
tar zxf sphinx-339e123.tar.gz 

编译：

./configure --with-php-config=/usr/bin/php-config --with-sphinx=/usr/local/sphinx/libsphinxclient
# 这个地方好大的坑，一直提示：Cannot find libsphinxclient headers
make && make install

```

```

PHP         : /usr/bin/php 
PHP_SAPI    : cli
PHP_VERSION : 7.0.27
ZEND_VERSION: 3.0.0
PHP_OS      : Linux - Linux vic 2.6.32-642.13.1.el6.x86_64 #1 SMP Wed Jan 11 20:56:24 UTC 2017 x86_64
INI actual  : /usr/local/src/sphinx-339e123/tmp-php.ini
More .INIs  :   
CWD         : /usr/local/src/sphinx-339e123
Extra dirs  : 
VALGRIND    : Not used
=====================================================================
TIME START 2018-03-29 11:11:47
=====================================================================
No tests were run.

```

搞了很久终于可以了；

最后一步：修改php.ini

```
extension = sphinx.so

然后重启php-fpm即可，执行php -m，看到有sphinx扩展说明安装成功
php -m 

终于安装成功了；
```

  

2.sphinx 用php的使用

```php

$sphinx = new SphinxClient;
$sphinx->setServer("localhost", 9312);
$sphinx->setMatchMode(SPH_MATCH_ANY);   //匹配模式 ANY为关键词自动拆词，ALL为不拆词匹配（完全匹配）
$sphinx->SetArrayResult ( true );    //返回的结果集为数组
$result = $sphinx->query("test","*");    //星号为所有索引源
$count=$result['total'];        //查到的结果条数
$time=$result['time'];            //耗时
$arr=$result['matches'];        //结果集
$id='';
for($i=0;$i<$count;$i++){
    $id.=$arr[$i]['id'].',';
}
$id=substr($id,0,-1);            //结果集的id字符串

```



[0]: http://git.php.net/?p=pecl/search_engine/sphinx.git