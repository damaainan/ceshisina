### windows 环境使用
程序目录加入 PATH 变量

    mongod -dbpath D:\mongo\data\db
启动  
另开窗口执行 mongo ，进入 shell  

MongoDB安装为windows服务:

    mongod --dbpath "D:\mongo\data\db" --logpath "D:\mongo\data\log\MongoDB.log" --install --serviceName "mongodb"

启动服务
    
    net start mongodb
停止服务

    net stop mongodb

删除服务

    mongod --dbpath "D:\mongo\data\db" --logpath "D:\mongo\data\log\MongoDB.log" --remove --serviceName "mongodb"

要是出现如下错误的话需要将db目录下的mongod.lock删除后再启动    

---

## 基本概念
## 文档

文档是MongoDB的核心概念，多个键及其关联的值有序地放置在一起便是文档。 在js里，文档表示为对象：

    {"greenting" : "Hello,world!"}
    

这个对象只有一个键"greeting"，其对应的值"Hello,world!"

文档的键是字符串，除少数例外情况，键可以使用任意UTF-8字符：

     - 键不能含有\0(空字符)。这个字符用来表示键的结尾
     - .和$有特别的意义，只有当特定环境下才能使用，通常来说是被保留了
     - 以下划线"_"开头的键是保留的，虽然不是严格要求的
    

MongoDB不单区分类型，也区分大小写，还有，MongoDB的文档不能有重复的键，例如下面的文档是非法的：

    {"greeting":"Hello,world!","greeting":"Hello,mongoDB!"}
    

## 集合

集合是一组文档。如果说MongoDB中的文档类似于关系型数据库中的行，那么集合就如同表。

### 无模式

集合是无模式的。这意味着集合里面的文档可以是各式各样的，例如下面两个文档可以存在于同一个集合里面：

    {"greeting": "Hello,world!"}
    {"foo": 5}
    

### 命名

我们可以通过名字来标示集合。集合名可以是满足下列条件的UTF-8条件

     - 集合名不能是空字符串""。
     - 集合名不能含有\0字符（空字符）
     - 集合名不能以"system."开头，这是为系统集合保留的前缀
     - 用户创建的集合名字不能含有保留字符$ 
    

### 子集合

组织集合的一种惯例是使用"."字符分开的按命名空间划分的子集合。

## Shell中的基本操作

在shell查看数据会用到4个基本操作：创建、读取、更新和删除（CRUD）

## 数据库

MongoDB中多个文档组成集合，同样多个集合可以组成数据库。一个MongoDB实例可以承载多个数据库，数据库名可以是满足以下条件的任意UTF-8字符串

     - 不能是空字符串("")
     - 不能含有''(空格)、.、$、/、\和\0(空字符)
     - 应全部小写
     - 最多64字节
    

## 数据类型

MongoDB支持许多数据类型的列表下面给出：

    String : 这是最常用的数据类型来存储数据。在MongoDB中的字符串必须是有效的UTF-8。
    
    Integer : 这种类型是用来存储一个数值。整数可以是32位或64位，这取决于您的服务器。
    
    Boolean : 此类型用于存储一个布尔值 (true/ false) 。
    
    Double : 这种类型是用来存储浮点值。
    
    Min/ Max keys : 这种类型被用来对BSON元素的最低和最高值比较。
    
    Arrays : 使用此类型的数组或列表或多个值存储到一个键。
    
    Timestamp : 时间戳。这可以方便记录时的文件已被修改或添加。
    
    Object : 此数据类型用于嵌入式的文件。
    
    Null : 这种类型是用来存储一个Null值。
    
    Symbol : 此数据类型用于字符串相同，但它通常是保留给特定符号类型的语言使用。
    
    Date : 此数据类型用于存储当前日期或时间的UNIX时间格式。可以指定自己的日期和时间，日期和年，月，日到创建对象。
    
    Object ID : 此数据类型用于存储文档的ID。
    
    Binary data : 此数据类型用于存储二进制数据。
    
    Code : 此数据类型用于存储到文档中的JavaScript代码。
    
    Regular expression : 此数据类型用于存储正则表达式
    

## MongoDB的基本命令

### use 命令

MongoDB use DATABASE_NAME 用于创建数据库。该命令将创建一个新的数据库，如果它不存在，否则将返回现有的数据库。

### dropDatabase() 方法

MongoDB db.dropDatabase() 命令是用来删除一个现有的数据库。  
dropDatabase() 命令的基本语法如下：

    db.dropDatabase()
    

### drop() 方法

MongoDB 的 db.collection.drop() 是用来从数据库中删除一个集合。

### insert() 方法

要插入数据到 MongoDB 集合，需要使用 MongoDB 的 insert() 或 save() 方法。

### find() 方法

要从MongoDB 查询集合数据，需要使用MongoDB 的 find() 方法。

### pretty() 方法

结果显示在一个格式化的方式，可以使用 pretty() 方法.

### Limit() 方法

要限制 MongoDB 中的记录，需要使用 limit() 方法。 limit() 方法接受一个数字型的参数，这是要显示的文档数。  
语法:

limit() 方法的基本语法如下

    >db.COLLECTION_NAME.find().limit(NUMBER)  
    

## MongoDB 数据转储

创建备份MongoDB中的数据库，应该使用mongodump命令。  
mongodump命令的基本语法如下：

    >mongodump
    

### 恢复数据

恢复备份数据使用MongoDB 的 mongorerstore 命令。此命令将恢复所有的数据从备份目录。  
语法：

mongorestore命令的基本语法

    >mongorestore

