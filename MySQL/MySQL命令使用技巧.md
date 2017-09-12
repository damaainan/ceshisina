# MySQL命令使用技巧|新手指引

 时间 2017-09-11 08:11:25 

原文[http://wubx.net/mysql-client-tips/][1]

<font face=微软雅黑>

## MySQL客户端读取配置文件的顺序

MySQL这个命令可以读取配置文件如下：

    #mysql --help |grep my.cnf

    Default options are read from the following files in the given order:
    
    /etc/my.cnf /etc/mysql/my.cnf /usr/local/mysql/etc/my.cnf ~/.my.cnf

按顺序加载，后面的会把前面的覆盖掉。可以识别配置文件中：

[client] [mysql] ，如下：

    [client]
    port            = 3306
    socket          = /tmp/mysql3306.sock
    
    [mysql]
    prompt="\\u@\\h [\\d]>"
    #pager="less -i -n -S"
    #tee=/opt/mysql/query.log
    no-auto-rehash

## 基本使用篇

#### 更改MySQL提示符

MySQL的传统提示符是：

    mysql>

这里推荐通过修改配置文件中[MySQL]的：prompt,方便了解连接到哪个MySQL哪个DB上。如下：

    [mysql]
    prompt="\\u@\\h [\\d]>"

更多参考可查看：man MySQL

      +-------+--------------------------------------------------------------------+
           |Option | Description                                                        |
           +-------+--------------------------------------------------------------------+
           |\C     | The current connection identifier (MySQL 5.7.6 and up)             |
           +-------+--------------------------------------------------------------------+
           |\c     | A counter that increments for each statement you issue             |
           +-------+--------------------------------------------------------------------+
           |\D     | The full current date                                              |
           +-------+--------------------------------------------------------------------+
           |\d     | The default database                                               |
           +-------+--------------------------------------------------------------------+
           |\h     | The server host                                                    |
           +-------+--------------------------------------------------------------------+
           |\l     | The current delimiter                                              |
           +-------+--------------------------------------------------------------------+
           |\m     | Minutes of the current time                                        |
           +-------+--------------------------------------------------------------------+
           |\n     | A newline character                                                |
           +-------+--------------------------------------------------------------------+
           |\O     | The current month in three-letter format (Jan, Feb, ...)           |
           +-------+--------------------------------------------------------------------+
           |\o     | The current month in numeric format                                |
           +-------+--------------------------------------------------------------------+
           |\P     | am/pm                                                              |
           +-------+--------------------------------------------------------------------+
           |\p     | The current TCP/IP port or socket file                             |
           +-------+--------------------------------------------------------------------+
           |\R     | The current time, in 24-hour military time (0–23)                  |
           +-------+--------------------------------------------------------------------+
           |\r     | The current time, standard 12-hour time (1–12)                     |
           +-------+--------------------------------------------------------------------+
           |\S     | Semicolon                                                          |
           +-------+--------------------------------------------------------------------+
           |\s     | Seconds of the current time                                        |
           +-------+--------------------------------------------------------------------+
           |\t     | A tab character                                                    |
           +-------+--------------------------------------------------------------------+
           | \U    |  Your full user_name@host_name account name                        |
           +-------+--------------------------------------------------------------------+
           |\u     | Your user name                                                     |
           +-------+--------------------------------------------------------------------+
           |\v     | The server version                                                 |
           +-------+--------------------------------------------------------------------+
           |\w     | The current day of the week in three-letter format (Mon, Tue, ...) |
           +-------+--------------------------------------------------------------------+
           |\Y     | The current year, four digits                                      |
           +-------+--------------------------------------------------------------------+
           |\y     | The current year, two digits                                       |
           +-------+--------------------------------------------------------------------+
           |\_     | A space                                                            |
           +-------+--------------------------------------------------------------------+
           |\      | A space (a space follows the backslash)                            |
           +-------+--------------------------------------------------------------------+
           |\'     | Single quote                                                       |
           +-------+--------------------------------------------------------------------+
           |\"     | Double quote                                                       |
           +-------+--------------------------------------------------------------------+
           |\\     | A literal \ backslash character                                    |
           +-------+--------------------------------------------------------------------+
           |\x     |                                                                    |
           |       |        x, for any “x” not listed above                             |
           +-------+--------------------------------------------------------------------+

动态的调整测试：

    mysql> prompt (\u@\h) [\d]>\_
    PROMPT set to '(\u@\h) [\d]>\_'
    (user@host) [database]>
    #想回到解放前原始样子，直接输入：prompt 就可以。
    (user@host) [database]> prompt
    Returning to default PROMPT of mysql>
    mysql>

### 使用login-path实现无密码登录 

MySQL 5.6后推出了mysql_config_editor这个命令，本地加密存储用户的密码，通过指定登录文中的某一个MySQL而不用输入密码进行登录。

使用方法： mysql_config_editor set –login-path=登录实例的名字 –host=ip –user=用户名 –password 输入密码即可。具体如下： 

    #mysql_config_editor set --login-path=3306 --host=127.0.0.1 --port=3306 --user=wubx --password
    Enter password:
    
    
    #mysql_config_editor print --all
    [3306]
    user = wubx
    password = *****
    host = 127.0.0.1
    port = 3306
    
    #mysql --login-path=3306             
    Welcome to the MySQL monitor.  Commands end with ; or \g.
    Your MySQL connection id is 33
    Server version: 5.7.19-log MySQL Community Server (GPL)
    
    Copyright (c) 2000, 2017, Oracle and/or its affiliates. All rights reserved.
    
    Oracle is a registered trademark of Oracle Corporation and/or its
    affiliates. Other names may be trademarks of their respective
    owners.
    
    Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.
    
    wubx@127.0.0.1:3306 [(none)]>

更多操作查看： mysql_config_editor –help

### 命令行执行SQL 

#### 通过命令行执行SQL方法有多种，以下三种较为常见：

* **通过 -e 参数指定SQL**

```sql
    #mysql -S /tmp/mysql3306.sock -uroot -pzhishutang.com  -e "select version()"
    mysql: [Warning] Using a password on the command line interface can be insecure.
    +------------+
    | version()  |
    +------------+
    | 5.7.19-log |
    +------------+
```

* **通管道来执行**

```sql
    #echo "select version()"|mysql -S /tmp/mysql3306.sock -uroot -pwubxwubx  zst
    mysql: [Warning] Using a password on the command line interface can be insecure.
    version()
    5.7.19-log
```

* **通过login-path进行上面的方操作**

```sql
    #echo "select version()"|mysql --login-path=3306
    version()
    5.7.19-log
    #mysql --login-path=3306 -e "select version()"
    +------------+
    | version()  |
    +------------+
    | 5.7.19-log |
    +------------+
     echo "select version()"|mysql --login-path=3306
```
## 高级进阶篇

### Pager使用 

#### 这算是MaSQL这个命令中的一个高级功能。

* **分屏显示**

```
    wubx@127.0.0.1:3306 [(none)]>pager more
    wubx@127.0.0.1:3306 [(none)]>pager less
```

* **结果用md5sum比较**

```
    wubx@127.0.0.1:3306 [(none)]>pager md5sum
    PAGER set to 'md5sum'
    wubx@127.0.0.1:3306 [(none)]>select * from information_schema.tables;
    56696bd844f2e5885ce9278d3beca750  -
```

* **结果中搜索**

```
    wubx@127.0.0.1:3306 [(none)]>pager grep  Sleep|wc -l;
    wubx@127.0.0.1:3306 [(none)]>show processlist;
```

* **不显示查询出来结果**

```
    wubx@127.0.0.1:3306 [(none)]>pager cat >>/dev/null
    wubx@127.0.0.1:3306 [(none)]>select * from information_schema.tables;
    408 rows in set (0.02 sec)
```

* **恢复 pager**

```
    wubx@127.0.0.1:3306 [(none)]>pager
    或是
    wubx@127.0.0.1:3306 [(none)]>nopager
```
### 记录MySQL输入的命令及结果 

#### 使用tee命令或是在配置文件配置，参考：

    wubx@127.0.0.1:3306 [(none)]>tee /tmp/mysql.log
    wubx@127.0.0.1:3306 [(none)]>select * from information_schema.tables;

输入点操作，观察一下/tmp/mysql.log吧

另外也可以通过在配置文件中，加这个配置 [mysql] tee=/tmp/mysql.log

再次登录即可 （前提这个配置文件是可以被mysql读到的）

### MySQL调用系统命令 

#### 该功能只能Linux平台支持，利用system后面跟命令调用，参考：

    wubx@127.0.0.1:3306 [(none)]>system top
    
    wubx@127.0.0.1:3306 [(none)]>system ps axu |grep mysqld
    mysql     5041  0.0 21.9 1077760 223936 pts/0  Sl   14:33   0:02 /usr/local/mysql/bin/mysqld --defaults-file=/data/mysql/mysql3306/my3306.cnf
    root      5368  0.0  0.1 106148  1052 pts/0    S+   15:41   0:00 sh -c  ps axu|grep mysqld
    root      5370  0.0  0.0 103360   816 pts/0    S+   15:41   0:00 grep mysqld

#### 感受一下吧，是不是很帅，更好的等你来发现。 

### 执行外面的SQL 

#### 利用source命令执行外面的SQL，如：

    echo "select version();" >/tmp/v.sql
    wubx@127.0.0.1:3306 [(none)]>source /tmp/v.sql;
    +------------+
    | version()  |
    +------------+
    | 5.7.19-log |
    +------------+
    1 row in set (0.00 sec)

### 使用binary模型批量执行 

#### 如果你在恢复数据时遇到：

    mysql -u root -p -h localhost -D zhishutang < dump.sql
    ERROR: ASCII '\0' appeared in the statement, but this is not allowed unless option --binary-mode is enabled and mysql is run in non-interactive mode. Set --binary-mode to 1 if ASCII '\0' is expected. Query: 'XXXX'.

#### 请更改为,在MySQL后面添加–binary-mode，如：

    mysql --binary-mode  -u root -p -h localhost -D zhishutang < dump.sql

## 后语

MySQL这个客端使用较多，你还有什么好玩的方法，欢迎回复，我会整理补充到这篇文章中，让更多刚接触MySQL的人员能快速地上手。

#### 作者：吴炳锡 来源：http://wubx.net/ 联系方式： wubingxi#163.com 转载请注明作/译者和出处，并且不能用于商业用途，违者必究.

</font>

[1]: http://wubx.net/mysql-client-tips/
