# 带您深入了解MySQL的权限管理

 时间 2017-10-31 11:30:30  

原文[http://database.51cto.com/art/201710/555876.htm][1]


MySQL 的权限表在数据库启动的时候就载入内存，当用户通过身份认证后，就在内存中进行相应权限的存取，这样，此用户就可以在数据库中做权限范围内的各种操作了。

#### 一、权限表的存取

在权限存取的两个过程中，系统会用到 “mysql” 数据库(安装 MySQL 时被创建，数据库名称叫“mysql”) 中 user、host 和 db 这3个最重要的权限表。

在这 3 个表中，最重要的表示 user 表，其次是 db 表，host 表在大多数情况下并不使用。

user 中的列主要分为 4 个部分：用户列、权限列、安全列和资源控制列。

通常用的最多的是用户列和权限列，其中权限列又分为普通权限和管理权限。普通权限用于数据库的操作，比如 select_priv、super_priv 等。

当用户进行连接时，权限表的存取过程有以下两个过程：

* 先从 user 表中的 host、user 和 password 这 3 个字段中判断连接的 IP、用户名、和密码是否存在于表中，如果存在，则通过身份验证，否则拒绝连接。
* 如果通过身份验证、则按照以下权限表的顺序得到数据库权限：user -> db -> tables_priv -> columns_priv。

在这几个权限表中，权限范围依次递减，全局权限覆盖局部权限。上面的第一阶段好理解，下面以一个例子来详细解释一下第二阶段。

为了方便测试，需要修改变量 sql_mode

```sql
    // sql_mode 默认值中有 NO_AUTO_CREATE_USER (防止GRANT自动创建新用户，除非还指定了密码) 
     
    SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';  
```

#### 1. 创建用户 zj@localhost，并赋予所有数据库上的所有表的 select 权限

```sql
    MySQL [mysql]> grant select on *.* to zj@localhost; 
    Query OK, 0 rows affected, 2 warnings (0.00 sec) 
     
    MySQL [mysql]> select * from user where user="zj" and host='localhost' \G; 
    *************************** 1. row *************************** 
                      Host: localhost 
                      User: zj 
               Select_priv: Y 
               Insert_priv: N 
               Update_priv: N 
               Delete_priv: N 
               Create_priv: N 
                 Drop_priv: N 
               Reload_priv: N 
    ...  
```

#### 2. 查看 db 表

```sql
    MySQL [mysql]> select * from db where user='zj' \G ; 
     
    Empty set (0.00 sec)  
```

可以发现，user 表的 select_priv 列是 “Y”，而 db 表中并没有记录，也就是说，对所有数据库都具有相同的权限的用户并不需要记录到 db 表，而仅仅需要将 user 表中的 select_priv 改为 “Y” 即可。换句话说，user 表中的每个权限都代表了对所有数据库都有权限。

#### 3. 将 zj@localhost 上的权限改为只对 t2 数据库上所有表的 select 权限。

```sql
    MySQL [mysql]> revoke select on *.* from zj@localhost; 
    Query OK, 0 rows affected, 1 warning (0.02 sec) 
     
    MySQL [mysql]> grant select on t2.* to zj@localhost; 
    Query OK, 0 rows affected, 1 warning (0.04 sec) 
     
    MySQL [mysql]> select * from user where user='zj' \G; 
    *************************** 1. row *************************** 
                      Host: localhost 
                      User: zj 
               Select_priv: N 
               Insert_priv: N 
               Update_priv: N 
               Delete_priv: N 
               Create_priv: N 
                 Drop_priv: N 
               Reload_priv: N 
    ... 
     
    MySQL [mysql]> select * from db where user='zj' \G; 
    *************************** 1. row *************************** 
                     Host: localhost 
                       Db: t2 
                     User: zj 
              Select_priv: Y 
              Insert_priv: N 
              Update_priv: N 
              Delete_priv: N 
              Create_priv: N 
                Drop_priv: N 
               Grant_priv: N  
```

这时候发现，user 表中的 select_priv 变为 “N” ，而 db 表中增加了 db 为 t2 的一条记录。也就是说，当只授予部分数据库某些权限时，user 表中的相应权限列保持 “N”，而将具体的数据库权限写入 db 表。table 和 column 的权限机制和 db 类似。

从上例可以看出，当用户通过权限认证，进行权限分配时，将按照 user -> db -> tables_priv -> columns_priv 的顺序进行权限分配，即先检查全局权限表 user，如果 user 中对应 权限为 “Y”，则此用户对所有数据库的权限都为“Y”，将不再检查 db、tables_priv 和 columns_priv;如果为“N”，则到 db 表中检查此用户对应的具体数据库，并得到 db 中为 “Y”的权限;如果 db 中相应权限为 “N”，则再依次检查tables_priv 和 columns_priv 中的权限，如果所有的都为“N”，则判断为不具备权限。

#### 二、账号管理

主要包括账号的创建，权限的更改和账号的删除。

#### 1. 创建账号

使用 grant 语法创建，示例：

#### (1) 创建用户 zj ，权限为可以在所有数据库上执行所有权限，只能从本地进行连接。

```sql
    MySQL [mysql]> grant all privileges on *.* to zj@localhost; 
    Query OK, 0 rows affected, 2 warnings (0.00 sec) 
     
    MySQL [mysql]> select * from user where user="zj" and host="localhost" \G; 
    *************************** 1. row *************************** 
                      Host: localhost 
                      User: zj 
               Select_priv: Y 
               Insert_priv: Y 
               Update_priv: Y 
               Delete_priv: Y 
               Create_priv: Y 
                 Drop_priv: Y 
               Reload_priv: Y 
             Shutdown_priv: Y  
```

可以发现，除了 grant_priv 权限外，所有权限在 user 表里面都是 “Y”。

#### (2) 在 (1) 基础上，增加对 zj 的 grant 权限

```sql
    MySQL [(none)]> grant all privileges on *.* to zj@localhost with grant option; 
    Query OK, 0 rows affected, 1 warning (0.01 sec) 
     
    MySQL [mysql]> select * from user where user="zj" and host='localhost' \G ; 
    *************************** 1. row *************************** 
                      Host: localhost 
                      User: zj 
               Select_priv: Y 
               Insert_priv: Y 
               Update_priv: Y 
               Delete_priv: Y 
               Create_priv: Y 
                 Drop_priv: Y 
               Reload_priv: Y 
             Shutdown_priv: Y 
              Process_priv: Y 
                 File_priv: Y 
                Grant_priv: Y 
    ...  
```

#### (3) 在 (2) 基础上，设置密码为 “123”

```sql
    MySQL [mysql]> grant all  privileges on *.* to zj@localhost identified by '123' with grant option; 
    Query OK, 0 rows affected, 2 warnings (0.01 sec) 
     
    MySQL [mysql]> select * from user where user="zj" and host="localhost" \G ; 
    *************************** 1. row *************************** 
                      Host: localhost 
                      User: zj 
               Select_priv: Y 
               Insert_priv: Y 
               Update_priv: Y 
               Delete_priv: Y 
               Create_priv: Y 
                 Drop_priv: Y 
               Reload_priv: Y 
    ......   
     authentication_string: *23AE809DDACAF96AF0FD78ED04B6A265E05AA257 
          password_expired: N 
     password_last_changed: 2017-09-25 20:29:42 
         password_lifetime: NULL  
```

可以发现，密码变成了一堆加密后的字符串。

#### (4) 创建新用户 zj2，可以从任何 IP 连接，权限为对 t2 数据库里的所有表进行 select 、update、insert 和 delete 操作，初始密码为“123”

```sql
    MySQL [mysql]> grant select ,insert, update,delete on t2.* to 'zj2'@'%' identified by '123'; 
    Query OK, 0 rows affected, 1 warning (0.00 sec) 
     
    MySQL [mysql]> select * from user where user='zj2' and host="%" \G; 
    *************************** 1. row *************************** 
                      Host: % 
                      User: zj2 
               Select_priv: N 
               Insert_priv: N 
               Update_priv: N 
               Delete_priv: N 
               Create_priv: N 
                 Drop_priv: N 
    ...... 
     authentication_string: *23AE809DDACAF96AF0FD78ED04B6A265E05AA257 
          password_expired: N 
     password_last_changed: 2017-09-25 20:37:49 
         password_lifetime: NULL 
     
    MySQL [mysql]> select * from db where user="zj2" and host='%' \G; 
    *************************** 1. row *************************** 
                     Host: % 
                       Db: t2 
                     User: zj2 
              Select_priv: Y 
              Insert_priv: Y 
              Update_priv: Y 
              Delete_priv: Y 
              Create_priv: N 
                Drop_priv: N 
    ......  
```

user 表中的权限都是“N”，db 表中增加的记录权限则都是“Y”。一般的，只授予用户适当的权限，而不会授予过多的权限。

本例中的 IP 限制为所有 IP 都可以连接，因此设置为 “*”，mysql 数据库中是通过 user 表的 host 字段来进行控制，host 可以是以下类型的赋值。

* Host 值可以是主机名或IP号，或 “localhost” 指出本地主机。
* 可以在 Host 列值使用通配符字符 “%” 和 “_”
* Host 值 “%” 匹配任何主机名，空 Host 值等价于 “%”，它们的含义与 like 操作符的模式匹配操作相同。

注意: mysql 数据库的 user 表中 host 的值为 “*” 或者空，表示所有外部 IP 都可以连接，但是不包括本地服务器 localhost，因此，如果要包括本地服务器，必须单独为 localhost 赋予权限。

#### (5) 授予 super、process、file 权限给用户 zj3@%

```sql
    MySQL [mysql]> grant super,process,file on *.* to 'zj3'@'%'; 
     
    Query OK, 0 rows affected, 1 warning (0.00 sec)  
```

因为这几个权限都是属于管理权限，因此不能够指定某个数据库，on 后面必须跟 “.”,下面语法将提示错误

```sql
    MySQL [mysql]> grant super,process,file on t2.* to 'zj3'@'%'; 
     
    ERROR 1221 (HY000): Incorrect usage of DB GRANT and GLOBAL PRIVILEGES  
```

#### (6) 只授予登录权限给 zj4@localhost

```sql
    MySQL [mysql]> grant usage on *.* to 'zj4'@'localhost'; 
    Query OK, 0 rows affected, 2 warnings (0.01 sec) 
     
    MySQL [mysql]> exit 
    Bye 
     
    zj@bogon:~$ mysql -uzj4 -p 
    Enter password:  
    Welcome to the MySQL monitor.  Commands end with ; or \g. 
    Your MySQL connection id is 78 
    Server version: 5.7.18-log Source distribution 
     
    Copyright (c) 2000, 2017, Oracle and/or its affiliates. All rights reserved. 
     
    Oracle is a registered trademark of Oracle Corporation and/or its 
    affiliates. Other names may be trademarks of their respective 
    owners. 
     
    Type 'help;' or '\h' for help. Type '\c' to clear the current input statement. 
     
    MySQL [(none)]> show databases; 
    +--------------------+ 
    | Database           | 
    +--------------------+ 
    | information_schema | 
    +--------------------+ 
    1 row in set (0.02 sec)  
```

usage 权限只能用于数据库登录，不能执行任何操作

#### 2. 查看账号权限

账号创建好后，可以通过如下命令查看权限:

```sql
    show grants for user@host; 
```

示例：

```sql
    MySQL [(none)]> show grants for zj@localhost; 
    +-------------------------------------------------------------------+ 
    | Grants for zj@localhost                                           | 
    +-------------------------------------------------------------------+ 
    | GRANT ALL PRIVILEGES ON *.* TO 'zj'@'localhost' WITH GRANT OPTION | 
    +-------------------------------------------------------------------+ 
    1 row in set (0.01 sec)  
```

#### 3. 更改账号权限

可以进行权限的新增和回收。和创建账号一样，权限变更也有两种方法：使用 grant(新增) 和 revoke (回收) 语句，或者更改权限表。

示例:

#### (1) zj4@localhost 目前只有登录权限

```sql
    MySQL [(none)]> show grants for zj4@localhost; 
    +-----------------------------------------+ 
    | Grants for zj4@localhost                | 
    +-----------------------------------------+ 
    | GRANT USAGE ON *.* TO 'zj4'@'localhost' | 
    +-----------------------------------------+ 
    1 row in set (0.00 sec)  
```

#### (2) 赋予 zj4@localhost 所有数据库上的所有表的 select 权限

```sql
    MySQL [(none)]> grant select on *.* to 'zj4'@'localhost'; 
    Query OK, 0 rows affected, 1 warning (0.00 sec) 
     
    MySQL [(none)]> show grants for zj4@localhost; 
    +------------------------------------------+ 
    | Grants for zj4@localhost                 | 
    +------------------------------------------+ 
    | GRANT SELECT ON *.* TO 'zj4'@'localhost' | 
    +------------------------------------------+ 
    1 row in set (0.00 sec)  
```

#### (3) 继续给 zj4@localhost 赋予 select 和 insert 权限，和已有的 select 权限进行合并

```sql
    MySQL [(none)]> show grants for 'zj4'@'localhost'; 
    +--------------------------------------------------+ 
    | Grants for zj4@localhost                         | 
    +--------------------------------------------------+ 
    | GRANT SELECT, INSERT ON *.* TO 'zj4'@'localhost' | 
    +--------------------------------------------------+ 
    1 row in set (0.00 sec)  
```

revoke 语句可以回收已经赋予的权限，对于上面的例子，这里决定要收回 zj4@localhost 上的 insert 和 select 权限：

```sql
    MySQL [(none)]> revoke select,insert on *.* from zj4@localhost; 
    Query OK, 0 rows affected, 1 warning (0.00 sec) 
     
    MySQL [(none)]> show grants for zj4@localhost; 
    +-----------------------------------------+ 
    | Grants for zj4@localhost                | 
    +-----------------------------------------+ 
    | GRANT USAGE ON *.* TO 'zj4'@'localhost' | 
    +-----------------------------------------+ 
    1 row in set (0.00 sec)  
```

usage 权限不能被回收，也就是说，revoke 用户并不能删除用户。

#### 4. 修改账号密码

#### (1) 可以用 mysqladmin 命令在命令行指定密码。

```sql
    shell> mysqladmin -u user_name -h host_name password "123456" 
```

#### (2) 执行 set password 语句。

```sql
    mysql> set password for 'username'@'%' = password('pwd'); 
```

如果是更改自己的密码，可以省略 for 语句

```sql
    mysql> set password=password('pwd'); 
```

#### (3) 可以在全局级别使用 grant usage 语句(在“.”)来指定某个账户的密码而不影响账户当前的权限。

```sql
    mysql> grant usage on *.* to 'username'@'%' identified by 'pwd'; 
```

#### 5. 删除账号

要彻底的删除账号，可以使用 drop user ：

```sql
    drop user zj@localhost; 
```

#### 6. 账号资源限制

创建 MySQL 账号时，还有一类选项称为账号资源限制，这类选项的作用是限制每个账号实际具有的资源限制，这里的“资源”主要包括：

* max_queries_per_hour count : 单个账号每小时执行的查询次数
* max_upodates_per_hour count : 单个账号每小时执行的更新次数
* max_connections_per_hour count : 单个账号每小时连接服务器的次数
* max_user_connections count : 单个账号并发连接服务器的次数


[1]: http://database.51cto.com/art/201710/555876.htm
