## Mysql 配置的工作原理


> 可能有时候我们会问，“我的服务器有50 GB内存，12核CPU，怎样配置最好？” 很遗憾，问题没这么简单，MySQL 服务器的配置应该符合它的工作负载，数据，以及应用需求，并不仅仅看硬件的情况。通常只需要把基本的项配置正确，应该将更多的时间花费在 schema 的优化，索引，以及查询设计上。

### 一、查找配置文件

为 Mysql 服务器创建一个靠谱的配置文件过程。  
MySQL是从命令行参数和配置文件获得配置信息。  
在类 UNIX 系统中，配置文件的位置一般在 `/etc/my.conf` 或者 `/etc/mysql/my.conf` 中。

一定要清楚的知道服务器配置文件的位置！有时候我们尝试修改配置文件但是不生效，因为修改的并不是服务器读取的文件。不过我们可以用下面的命令来找出正在运行的mysql的配置文件的位置。

    ➜  ~ which mysqld
    /usr/local/bin/mysqld
    ➜  ~ /usr/local/bin/mysqld --verbose --help | grep -A 1 'Default options'
    Default options are read from the following files in the given order:
    /etc/my.cnf /etc/mysql/my.cnf /usr/local/etc/my.cnf ~/.my.cnf
    ➜  ~

### 二、语法、作用域和动态性

#### 1.语法

配置项设置都使用小写，单词之间用下划线或横线隔开。下面的例子是等价的，并且可能在命令行和配置文件中都看到这两种格式：

    /usr/local/bin/mysqld --auto-increment-offset=5
    /usr/local/bin/mysqld --auto_increment_offset=5

在这里我们建议使用一种固定的风格。

#### 2.配置项作用域

配置项可以有多个作用域。有些设置是服务器级的（全局作用域），有些对每个连接是不同的（会话作用域），剩下的一些是对象级的。

许多会话级变量跟全局变量相等，可以认为是默认值。如果改变会话级变量，它只影响改动的当前连接，当连接关闭时所有的参数变更都会失效。

举例：

* `query_cache_size` 变量是全局的
* `sort_buffer_size` 变量默认是全局相同的，但每个线程里也可以设置

另外，除了在配置文件中设置变量，有很多变量（但不是所有）也可以在服务器运行时修改。MySQL 把这些归为动态配置变量。

    SET sort_buffer_size = 2000;

如果动态的设置变量，要注意 MySQL 关闭时可能丢失这些设置，如果想保持这些设置，还是需要修改配置文件。

如果在服务器运行时修改了变量的全局值，这个值对当前会话和其他任何已经存在的会话都不起效果，这是因为会话的变量值是在连续创建时从全局值初始化来的。在每次变更后，应该检查 SHOW_GLOBAL_VARIABLES 的输出,确认已经按照期望变更了。

#### 3.设置变量的副作用

动态设置变量可能导致意外的副作用，例如从缓冲中刷新脏块。务必小心那些可以在线更改的设置，因为它们可能导致数据库做大量的工作。

**常用的变量：**

    key_buffer_size      键缓冲区 
    table_cache_size     表可以被缓存的数量
    thread_cache_size    线程缓存
    query_cache_size     查询缓存
    read_buffer_size     
    sort_buffer_size      排序操作缓存分配内存




## mysql 配置优化

#### 开启mysql慢查询

    show [session|global] status '值';
    session：当前会话
    global：全局会话(Mysql启动到现在)
    
    
    # Mysql 启用时间
    MySQL > show status like 'uptime';
    
    # 查询次数
    MySQL > show status like 'com_select';
    
    # 添加次数
    MySQL > show status like 'com_insert';
    
    # 更新次数
    MySQL > show status like 'com_delete';
    
    # 删除次数
    MySQL > show status like 'com_delete';
    
    # 连接次数
    MySQL > show status like 'connections';
    
    # 慢查询次数
    MySQL > show status like 'slow_queries';
    
    # 查询慢查询时间（默认10秒）
    MySQL > show variables like 'long_query_tiem';
    
    # 设置慢查询时间
    MySQL > set long_query_time=1;

#### 数据库备份

    # 备份数据库
    # -l
    # -F 刷新bin-log日志
    # -d 没有数据,只导出表结构
    # --add-drop-table 在每个create语句之前增加一个drop table
    /usr/local/mysql/bin/mysqldump -h127.0.0.1 -uroot -p密码 数据库名 -l -F > /data/ceshi.sql
    /usr/local/mysql/bin/mysqldump -h127.0.0.1 -uroot -p密码 -d --add-drop-table 数据库名 > /data/ceshi.sql
    
    # 导入数据库
    # -v 查看导入详细信息
    # -f 遇到错误直接跳过，继续执行
    /usr/local/mysql/bin/mysql -h127.0.0.1 -uroot -pwoshishui ceshi -v -f </data/ceshi.sql
    
    # 回复bin-log日志数据到数据库
    # --start-position  开始位置
    # --stop-position   结束位置
    /usr/local/mysql/bin/mysqlbinlog --no-defaults mysql-bin.000008 |/usr/local/mysql/bin/mysql -uroot -pwoshishui ceshi
    
    /usr/local/mysql/bin/mysqlbinlog --no-defaults --start-position="500" --stop-position="600" mysql-bin.000008 |/usr/local/mysql/bin/mysql -uroot -pwoshishui ceshi
    
    # 查看big-log日志
    /usr/local/mysql/bin/mysqlbinlog --no-defaults mysql-bin.000008
    
    # 刷新日志
    MySQL > flush logs;
    
    # 查看bin-log日志
    MySQL > show master status;

