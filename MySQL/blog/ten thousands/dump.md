## 

    
    
    /* 备份与还原 */ ------------------
    备份，将数据的结构与表内数据保存起来。
    利用 mysqldump 指令完成。
    
    -- 导出
    **1**. 导出一张表
    　　mysqldump -u用户名 -p密码 库名 表名 > 文件名(D:/a.sql)
    **2**. 导出多张表
    　　mysqldump -u用户名 -p密码 库名 表1 表2 表3 > 文件名(D:/a.sql)
    **3**. 导出所有表
    　　mysqldump -u用户名 -p密码 库名 > 文件名(D:/a.sql)
    **4**. 导出一个库 
    　　mysqldump -u用户名 -p密码 -B 库名 > 文件名(D:/a.sql)
    
    可以-w携带备份条件
    
    -- 导入
    **1**. 在登录mysql的情况下：
    　　source  备份文件
    **2**. 在不登录的情况下
    　　mysql -u用户名 -p密码 库名 < 备份文件
    

**备份MySQL数据库的命令**

    mysqldump -hhostname -uusername -ppassword databasename > backupfile.sql

**备份MySQL数据库为带删除表的格式**备份MySQL数据库为带删除表的格式，能够让该备份覆盖已有数据库而不需要手动删除原有数据库。

    mysqldump ---add-drop-table -uusername -ppassword databasename > backupfile.sql

**直接将MySQL数据库压缩备份**

    mysqldump -hhostname -uusername -ppassword databasename | gzip > backupfile.sql.gz

**备份MySQL数据库某个(些)表**

    mysqldump -hhostname -uusername -ppassword databasename specific_table1 specific_table2 > backupfile.sql

**同时备份多个MySQL数据库**

    mysqldump -hhostname -uusername -ppassword --databases databasename1 databasename2 databasename3 > multibackupfile.sql

**仅仅备份数据库结构**

    mysqldump --no-data --databases databasename1 databasename2 databasename3 > structurebackupfile.sql

**备份服务器上所有数据库**

    mysqldump --all-databases  allbackupfile.sql

**还原MySQL数据库的命令**

    mysql -hhostname -uusername -ppassword databasename < backupfile.sql

**还原压缩的MySQL数据库**

    gunzip < backupfile.sql.gz | mysql -uusername -ppassword databasename

**将数据库转移到新服务器**

    mysqldump \-uusername \-ppassword databasename \| mysql \--host=*.*.*.\* \-C databasename

压缩备份 

**备份并用gzip压缩：**

    mysqldump < mysqldump options> | gzip > outputfile.sql.gz

**从gzip备份恢复：**

    gunzip < outputfile.sql.gz | mysql < mysql options>

**备份并用bzip压缩：**

    mysqldump < mysqldump options> | bzip2 > outputfile.sql.bz2

**从bzip2备份恢复:**

    bunzip2 < outputfile.sql.bz2 | mysql < mysql options> 

我的常用操作

**备份库**

    mysqldump –h127.0.0.1  -P330 –uroot –p –B dbname > 备份文件

可以省略–B作为选项，表示不创建库，只备份库内的所有的表

**备份表**

    mysqldump –h127.0.0.1  -P330 –uroot –p –B dbname tablename > 备份文件

与备份库相比，多出了一个表名的值：

**还原，执行外部文件内sql语句**

可以在mysql客户单登陆后，使用source 指令，来强制执行一个文件内的sql语句！

如果没有登陆可以选择采下面的形式：

    mysql -h127.0.0.1 -P3306 -uroot -p 库名

登陆后直接选择数据库

    mysql -h127.0.0.1 -P3306 -uroot -p 库名< e:/itcast_student_class.sql

表示，登陆后，选择数据库，并执行sql文件内的语句。




**mysqldump命令**是 mysql 数据库中备份工具，用于将MySQL服务器中的数据库以标准的sql语言的方式导出，并保存到文件中。 

### 语法  
    mysqldump(选项)

### 选项  
```
--add-drop-table：在每个创建数据库表语句前添加删除数据库表的语句；  
--add-locks：备份数据库表时锁定数据库表；  
--all-databases：备份MySQL服务器上的所有数据库；  
--comments：添加注释信息；  
--compact：压缩模式，产生更少的输出；  
--complete-insert：输出完成的插入语句；  
--databases：指定要备份的数据库；  
--default-character-set：指定默认字符集；  
--force：当出现错误时仍然继续备份操作；  
--host：指定要备份数据库的服务器；  
--lock-tables：备份前，锁定所有数据库表；  
--no-create-db：禁止生成创建数据库语句；  
--no-create-info：禁止生成创建数据库库表语句；  
--password：连接MySQL服务器的密码；  
--port：MySQL服务器的端口号；  
--user：连接MySQL服务器的用户名。
```
### 实例  
**导出整个数据库**

    mysqldump -u 用户名 -p 数据库名 > 导出的文件名  
    mysqldump -u [Linux][0]de -p smgp_apps_[Linux][0]de > [Linux][0]de.sql

**导出一个表**

    mysqldump -u 用户名 -p 数据库名 表名> 导出的文件名  
    mysqldump -u [Linux][0]de -p smgp_apps_[Linux][0]de users > [Linux][0]de_users.sql

**导出一个数据库结构**

    mysqldump -u [Linux][0]de -p -d --add-drop-table smgp_apps_[Linux][0]de > [Linux][0]de_db.sql

-d没有数据，–add-drop-table每个create语句之前增加一个drop table

[0]: http://codecloud.net/tag/Linux

