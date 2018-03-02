## 【mongoDB基础篇①】安装与常用操作语句

来源：[https://segmentfault.com/a/1190000004181765](https://segmentfault.com/a/1190000004181765)


## 简述

mongoDB与redis同为noSql数据库,但是redis为 **`kv数据库(key/value)`** ,而mongoDB为 **`文档型数据库`** 存储的是文档(Bson->json的二进制化).内部执行引擎为JS解释器, 把文档存储成bson结构,在查询时,转换为JS对象,并可以通过熟悉的js语法来操作
## mongoDB的安装启动

在linux上直接下载解压运行即可,本身是已编译好的二进制可执行文件.
如果报错
`-bash: /usr/local/mongodb/bin/mongod: cannot execute binary file`
说明你的服务器和mongodb 的版本不对应, 如果服务器是64位,下载x86_64的mongodb ,如果服务器是32位的, 下载i686的mongodb/
### BIN目录说明

```LANG
bsondump 导出BSON结构
mongo 客户端
mongod 服务端
mongodump 整体数据库二进制导出
mongoexport 导出易识别的json文档或csv文档
mongorestore 数据库整体导入
mongos 路由器(分片用)
mongofiles GridFS工具，内建的分布式文件系统
mongoimport 数据导入程序
mongotop 运维监控
mongooplog 
mongoperf
mongostat

```
### 启动

```LANG
#启动服务端
./mongod --dbpath /usr/local/mongodb/data --logpath /usr/local/mongodb/logs/mongo.log --fork --port 27017  

#启动客户端
./mongo
./mongo --host xxx -u adminUserName -p userPassword --authenticationDatabase admin #可远程登录并指定登录用户以及数据库目录
```

**`注意:`** 

* 关于日志文件和数据存储路径的指定一定要是正确的，否则就会报如下错误

```LANG
ERROR: child process failed, exited with error number 1
```


* mongodb非常的占磁盘空间, 刚启动后要占3-4G左右,如果你用虚拟机练习,可能空间不够,导致无法启动.可以用 --smallfiles 选项来启动, 将会占用较小空间,大约400M左右.

* 32位的mongo客户端貌似会出现`段错误(segmentation fault)`

* mongodb服务器启动时, 默认不是需要认证的.
要让用户生效, 需要启动服务器时,就指定`--auth`选项.这样, 操作时,就需要认证了(另外也可以在其配置文件中指定)



参数解释:

```LANG
--dbpath 数据存储目录
--logpath 日志存储目录
--port 运行端口(默认27017)
--fork 后台进程运行

```
## mongodb 命令行
### 系统与库表级命令
#### 查看当前的数据库

```LANG
show dbs/databases

```

注意:`databases` 这个命令只存在在新版本中。
#### 选库/创建库

Mongodb的库是隐式创建,你可以use 一个不存在的库
然后在该库下创建collection,即可创建库

```LANG
use databaseName

```
#### 创建collection

```LANG
db.createCollection('collectionName') 

```

collection同样允许隐式创建:

```LANG
db.collectionName.insert(json);

```
#### 查看当前库下的collection

```LANG
show tables/collections 

```
#### 删除collection

```LANG
db.collectionName.drop()

```
#### 删除database

```LANG
db.dropDatabase() # 选择之后使用

```
#### 显示用户

```LANG
show users

```
#### help命令

```LANG
db.help()
db.youColl.find().help();

```
### DML命令
#### insert/save

介绍: mongodb存储的是文档,文档是json格式的对象.
语法: db.collectionName.isnert(document);

```LANG
# 增加单篇文档,默认有一个ID
db.collectionName.insert({title:'nice day'});

# 增加单个文档,并指定_id
db.collectionName.insert({_id:8,age:78,name:'lisi'});

# 增加多个文档
db.collectionName.insert(
    [
        {time:'friday',study:'mongodb'},
        {_id:9,gender:'male',name:'QQ'}
    ]
)

# insert 与 save的区别 : 如果插入的数据的_id相同,save将会更新该文档,而insert将会报错
> db.user.find();
{ "_id" : 6, "sex" : "nan" }
{ "_id" : 1, "name" : "zxg" }
{ "_id" : 2, "name" : "user2", "age" : 2 }
{ "_id" : 3, "name" : "user3", "age" : 3 }

> db.user.insert({_id:3,name:'zhouzhou'});
WriteResult({
        "nInserted" : 0,
        "writeError" : {
                "code" : 11000,
                "errmsg" : "E11000 duplicate key error index: user.user.$_id_ dup key: { : 3.0 }"
        }
})

> db.user.save({_id:3,name:'zhouzhou'});
WriteResult({ "nMatched" : 1, "nUpserted" : 0, "nModified" : 1 })

> db.user.find();
{ "_id" : 6, "sex" : "nan" }
{ "_id" : 1, "name" : "zxg" }
{ "_id" : 2, "name" : "user2", "age" : 2 }
{ "_id" : 3, "name" : "zhouzhou" }
```
#### remove

语法: db.collection.remove(查询表达式, 选项);
选项是指  {justOne:true/false},是否只删一行, 默认为false

注意

* 查询表达式依然是个json对象

* 查询表达式匹配的行,将被删掉.

* 如果不写查询表达式,collections中的所有文档将被删掉.`在最新版本的mongodb,必须要写表达式才可以删除`



```LANG
> db.user.find()
{ "_id" : 1, "name" : "user1","age" : 1 }
{ "_id" : 2, "name" : "user2","age" : 2 }
{ "_id" : 3, "name" : "user3","age" : 3 }
{ "_id" : 4, "name" : "user4","age" : 4 }
{ "_id" : 5, "name" : "user5","age" : 5 }
{ "_id" : 6, "sex" : "nan" }

> db.user.remove({name:/user*/i},1)  # 可以通过正则删除,并且只删除第1行

> db.user.find()
{ "_id" : 2, "name" : "user2","age" : 2 }
{ "_id" : 3, "name" : "user3","age" : 3 }
{ "_id" : 4, "name" : "user4","age" : 4 }
{ "_id" : 5, "name" : "user5","age" : 5 }
{ "_id" : 6, "sex" : "nan" }

> db.user.remove({name:/user*/i}); # 删除所有user开头的

> db.user.remove({age:{$gte:4}}); # 删除年龄大于或等于4的,这里的查询表达式参考下文的find部分,会有详细的说明
```
#### update

语法: db.collection.update(criteria,objNew,upsert,multi)

* criteria: 设置查询条件,用于查询哪些文档需要被更新.这里可以组合非常复杂的查询条件

* objNew: 更新后的对象

* upsert: 设置为真的时候如果记录已经存在,更新它,否则新增一个记录 默认为false

* multi: 设置为真的时候,将会更新所有符合查询条件的文档.在mongodb中默认情况下只会更新第一条符合的文档.



1.错误的更改

```LANG
> db.user.find();
{ "_id" : 6, "sex" : "nan" }
{ "_id" : 1, "name" : "user1", "age" : 1 }
{ "_id" : 2, "name" : "user2", "age" : 2 }
{ "_id" : 3, "name" : "user3", "age" : 3 }

> db.user.update({name:'user1'},{name:'zxg'});
WriteResult({ "nMatched" : 1, "nUpserted" : 0, "nModified" : 1 })

> db.user.find();
{ "_id" : 6, "sex" : "nan" }
{ "_id" : 1, "name" : "zxg" }
{ "_id" : 2, "name" : "user2", "age" : 2 }
{ "_id" : 3, "name" : "user3", "age" : 3 }

> db.user.update({name:'xb'},{name:'jyh'},1); # 没有就添加
WriteResult({
    "nMatched" : 0,
    "nUpserted" : 1,  # upserted 表示更新失败后插入;
    "nModified" : 0,
    "_id" : ObjectId("55bf37d6ea4ed1b30ffb368d")
})

{ "_id" : ObjectId("55bf3480ea4ed1b30ffb368c"), "name" : "jyh" }

> db.user.update({name:'jyh'},{name:'zxg'},1,1); #全部更改,但是报错,多条更新必须要有表达式
"writeError" : {
    "code" : 9,
    "errmsg" : "multi update only works with $ operators"
}
```

结果: 文档中的其他列也不见了,改后只有_id和name列了.
即--新文档直接替换了旧文档,而不是修改

2.通过修改表达式正确修改

```LANG
$set  # 当文档中包含该字段的时候,更新该字段,如果该文档中没有该字段,则为本文档添加一个字段.

$unset # 删除文档中的一个字段.

$rename # 重命名某个列

$inc # 增长某个列

$setOnInsert # 当upsert为true时,并且发生了insert操作时,可以补充的字段

$push # 将一个数字存入一个数组,分为三种情况,如果该字段存在,则直接将数字存入数组.如果该字段不存在,创建字段并且将数字插入该数组.如果更新的字段不是数组,会报错的.

$pushAll # 将多个数值一次存入数组.上面的push只能一个一个的存入

$addToSet # 与$push功能相同将一个数字存入数组,不同的是如果数组中有这个数字,将不会插入,只会插入新的数据,同样也会有三种情况,与$push相同.

$pop #删除数组最后一个元素

$pull # 删除数组中的指定的元素,如果删除的字段不是数组,会报错

$pullAll # 删除数组中的多个值,跟pushAll与push的关系类似.
```

update有以上条件操作符以后才能使用第4参数进行多文档更新操作;

```LANG
# set 仅更改文档中某列的值
db.user.update({name:'user1'},{$set:{name:'zxg'}},0,1); # 把所有name为user1的更改为zxg,仅更改此列

# unset 删除文档某一列
db.user.update({name:'zxg'},{$unset:{age:1}})

# 把name为zxg的列的列名name更改为xm
db.user.update({name:'zxg'},{$rename: {name:'xm'}})

# 减少年龄1岁
db.user.update({name:'user2'},{$inc:{age:-1}});

# 增长年龄1岁
db.user.update({name:'user2'},{$inc:{age:1}});

# 更新_id为7的文档,如果该文档不存在就创建并增加work字段,并指定值(可多个值指定),update第三参数upsert必须为true;
db.user.update({_id:7},{$setOnInsert:{work:'go on'}},1)
WriteResult({ "nMatched" : 0, "nUpserted" : 1, "nModified" : 0, "_id" : 7 })
```
##### 数组操作

```LANG
# $push
db.test.find()
{ "_id" : 1, "ary" : [ 1, 2, 3, 4 ] }
{ "_id" : 2, "text" : "test" }

db.test.update({_id:1},{$push:{ary:5}}) # 数组存在 直接压入，但是这个地方如果是数组的话就压入一个数组，并非是合并数组中的元素

db.test.update({_id:1},{$push:{ary:[8,9,10]}})

db.test.find()
{ "_id" : 2, "text" : "test" }
{ "_id" : 1, "ary" : [ 1, 2, 3, 4, 5,[8,9,10] ] } # 由此可见push一次只能插入一个字段,如果想要批量插入的话就缓存pushAll;

db.test.update({_id:2},{$push:{ary:6}}) # 数组不存在,创建数组并存入

db.test.find()
{ "_id" : 2, "ary" : [ 6 ], "text" : "test" }
{ "_id" : 1, "ary" : [ 1, 2, 3, 4, 5 ] }

db.test.update({_id:2},{$push:{text:6}})  # 更新字段存在但不是数组报错
Cannot apply $push/$pushAll modifier to non-array

# pop
db.user.update({_id:9},{$pop:{test:0}}) # 这里的test无论传入什么值,都是删掉test数组的最后一个

# $pull
db.user.update({_id:9},{$pull:{test:2}}) #这里的test传什么值就删掉什么值
```
### 自增唯一性ID方案findandmodify

在查询的同时进行更改

```LANG
db.collection.findAndModify({
    query: <document>, // 查询过滤条件
    
    sort: <document>, //如果多个文档符合查询过滤条件，将以该参数指定的排列方式选择出排在首位的对象
    
    remove: <boolean>, // Must specify either the remove or the update field. Removes the document specified in the query field. Set this to true to remove the selected document . The default is false.
    
    update: <document>, // Must specify either the remove or the update field. Performs an update of the selected document. The update field employs the same update operators or field: value specifications to modify the selected document.
    
    new: <boolean>, //  Optional. When true, returns the modified document rather than the original. The findAndModify() method ignores the new option for remove operations. The default is false.
   
    fields: <document>, //Optional. A subset of fields to return. The fields document specifies an inclusion of a field with 1, as in: fields: { <field1>: 1, <field2>: 1, ... }
   
    upsert: <boolean> //Optional. Used in conjunction with the update field. When true, findAndModify() creates a new document if no document matches the query, or if documents match the query, findAndModify() performs an update. To avoid multiple upserts, ensure that the query fields are uniquely indexed.The default is false.
});
```

```LANG
db.people.findAndModify({
    query: { name: "Tom", state: "active", rating: { $gt: 10 } },
    sort: { rating: 1 },
    update: { $inc: { score: 1 } }
})

db.runCommand({ findandmodify : "users", 
    query: {age: {$gte: 25}}, 
    sort: {age: -1}, 
    update: {$set: {name: 'a2'}, $inc: {age: 2}},
    remove: true
});
```
#### 自增ID处理

```LANG
> db.unique.insert({id:0});
WriteResult({ "nInserted" : 1 })
> db.unique.findAndModify({update:{$inc:{id:1}} })
{"id" : 0 }
> db.unique.findAndModify({update:{$inc:{id:1}} })
{"id" : 1 }

//获得ID以后就插入到需要有自增ID的collection中
```
### 深入查询表达式
#### 查找某集合所有文档

```LANG
db.collection.find()

```
#### 等值查询

```LANG

db.collection.find({filed:value})

```
#### 返回文档的某些值

```LANG

db.user.find({name:"user0"},{age:1})  
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "age" : 0 }  
{ "_id" : ObjectId("5198c3cac686eb50e2c843bd"), "age" : 20 }
#_id是默认显示的,可以传入_id:0来隐藏它

```
#### 不等于`$ne`

```LANG
db.collection.find({filed:{$ne:value}})

```
#### not in`$nin`

```LANG
db.collection.find({filed:{$nin:[value1,value2,value3]}})

```
#### 数组查询`$all $in`

* `$all`　数组中必须包含所有给定的查询的元素

* `$in`　数组中只要包含给定的查询元素就可以



```LANG
> db.phone.find()
{ "_id" : ObjectId("5198e20220c9b0dc40419385"), "num" : [ 1, 2, 3 ] }
{ "_id" : ObjectId("5198e21820c9b0dc40419386"), "num" : [ 4, 2, 3 ] }
{ "_id" : ObjectId("5198e22120c9b0dc40419387"), "num" : [ 1, 2, 5 ] }
> db.phone.find({num:{$all:[1,2]}})
{ "_id" : ObjectId("5198e20220c9b0dc40419385"), "num" : [ 1, 2, 3 ] }
{ "_id" : ObjectId("5198e22120c9b0dc40419387"), "num" : [ 1, 2, 5 ] }
> db.phone.find({num:{$all:[1,4]}})　# 同时包含１，４的没有数据
> db.phone.find({num:{$in:[1,4]}})　# 包含１或４的数据
{ "_id" : ObjectId("5198e20220c9b0dc40419385"), "num" : [ 1, 2, 3 ] }
{ "_id" : ObjectId("5198e21820c9b0dc40419386"), "num" : [ 4, 2, 3 ] }
{ "_id" : ObjectId("5198e22120c9b0dc40419387"), "num" : [ 1, 2, 5 ] }
```
#### 查找包含该字段的文档`$exists`

```LANG
> db.phone.find()
{ "_id" : ObjectId("5198e20220c9b0dc40419385"), "num" : [ 1, 2, 3 ] }
{ "_id" : ObjectId("5198e21820c9b0dc40419386"), "num" : [ 4, 2, 3 ] }
{ "_id" : ObjectId("5198e22120c9b0dc40419387"), "num" : [ 1, 2, 5 ] }
{ "_id" : ObjectId("5198e51a20c9b0dc40419388"), "state" : 1 }

> db.phone.find({state:{$exists:1}})　# 存在state字段的
{ "_id" : ObjectId("5198e51a20c9b0dc40419388"), "state" : 1 }

> db.phone.find({state:{$exists:0}})　# 不存在state字段的文档
{ "_id" : ObjectId("5198e20220c9b0dc40419385"), "num" : [ 1, 2, 3 ] }
{ "_id" : ObjectId("5198e21820c9b0dc40419386"), "num" : [ 4, 2, 3 ] }
{ "_id" : ObjectId("5198e22120c9b0dc40419387"), "num" : [ 1, 2, 5 ] }
```
#### 取模操作`$mod`

```LANG
> db.user.find()
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b8"), "name" : "user6", "age" : 6 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b9"), "name" : "user7", "age" : 7 }
{ "_id" : ObjectId("5198c286c686eb50e2c843ba"), "name" : "user8", "age" : 8 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bb"), "name" : "user9", "age" : 9 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bc"), "name" : "user10", "age" : 10 }
{ "_id" : ObjectId("5198c3cac686eb50e2c843bd"), "name" : "user0", "age" : 20 }

> db.user.find({age:{$mod:[3,1]}})  # 模三余一
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b9"), "name" : "user7", "age" : 7 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bc"), "name" : "user10", "age" : 10 }
```

同样使用 db.user.find("this.age%3==1")这个语句也能达到上面的效果,但是不推荐.
#### 满足一个`$or`

```LANG
> db.user.find()
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b8"), "name" : "user6", "age" : 6 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b9"), "name" : "user7", "age" : 7 }
{ "_id" : ObjectId("5198c286c686eb50e2c843ba"), "name" : "user8", "age" : 8 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bb"), "name" : "user9", "age" : 9 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bc"), "name" : "user10", "age" : 10 }
{ "_id" : ObjectId("5198c3cac686eb50e2c843bd"), "name" : "user0", "age" : 20 }

> db.user.find({$or:[{name:"user1"},{age:20}]}) # 由此可见or的键值为一个数组
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c3cac686eb50e2c843bd"), "name" : "user0", "age" : 20 }
```
#### 都不满足(排除)`$nor`

```LANG
> db.user.find({$nor:[{name:"user1"},{age:20}]}) # name不等于user1,以及age不等于20,可以理解为排除;
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b8"), "name" : "user6", "age" : 6 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b9"), "name" : "user7", "age" : 7 }
{ "_id" : ObjectId("5198c286c686eb50e2c843ba"), "name" : "user8", "age" : 8 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bb"), "name" : "user9", "age" : 9 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bc"), "name" : "user10", "age" : 10 }
```
#### 查询数组的长度等于给定数组长度的文档`$size`

```LANG
> db.phone.find()
{ "_id" : ObjectId("5198e20220c9b0dc40419385"), "num" : [ 1, 2, 3 ] }
{ "_id" : ObjectId("5198e21820c9b0dc40419386"), "num" : [ 4, 2, 3 ] }
{ "_id" : ObjectId("5198e22120c9b0dc40419387"), "num" : [ 1, 2, 5 ] }
{ "_id" : ObjectId("5198e51a20c9b0dc40419388"), "state" : 1 }
{ "_id" : ObjectId("519969952b76790566165de2"), "num" : [ 2, 3 ] }

> db.phone.find({num:{$size:4}}) # num数组长度为4的结果没有

> db.phone.find({num:{$size:3}}) # 长度为3的有三个
{ "_id" : ObjectId("5198e20220c9b0dc40419385"), "num" : [ 1, 2, 3 ] }
{ "_id" : ObjectId("5198e21820c9b0dc40419386"), "num" : [ 4, 2, 3 ] }
{ "_id" : ObjectId("5198e22120c9b0dc40419387"), "num" : [ 1, 2, 5 ] }
```
#### 自定义的查询`$where`

由bson转换为json,然后再通过回调函数去判断,性能很差,能不用尽量别用

```LANG
> db.user.find()
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b8"), "name" : "user6", "age" : 6 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b9"), "name" : "user7", "age" : 7 }
{ "_id" : ObjectId("5198c286c686eb50e2c843ba"), "name" : "user8", "age" : 8 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bb"), "name" : "user9", "age" : 9 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bc"), "name" : "user10", "age" : 10 }
{ "_id" : ObjectId("5198c3cac686eb50e2c843bd"), "name" : "user0", "age" : 20 }

> db.user.find({$where:function(){return this.age == 3 || this.age == 4}}) # 回调,进入了隐式迭代,然后符合条件的才返回;
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }

# 如今的新版本也可以直接写where条件
db.goods.find({$where:'this.cat_id != 3 && this.cat_id != 11'});
```
#### 根据数据类型查询`$type`

在mongodb中每一种数据类型都有对应的数字,我们在使用$type的时候需要使用这些数字,文档中给出如下的表示

类型|编号|
|:-:|:-:|
|双精度|1|
|字符串|2|
|对象|3|
|数组|4|
|二进制数据|5|
|对象 ID|7|
|布尔值|8|
|日期|9|
|空|10|
|正则表达式|11|
|JavaScript|13|
|符号|14|
|JavaScript（带范围）|15|
|32 位整数|16|
|时间戳|17|
|64 位整数|18|
|最小键|255|
|最大键|127|

```LANG
> db.user.find()
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b8"), "name" : "user6", "age" : 6 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b9"), "name" : "user7", "age" : 7 }
{ "_id" : ObjectId("5198c286c686eb50e2c843ba"), "name" : "user8", "age" : 8 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bb"), "name" : "user9", "age" : 9 }
{ "_id" : ObjectId("5198c286c686eb50e2c843bc"), "name" : "user10", "age" : 10 }
{ "_id" : ObjectId("5198c3cac686eb50e2c843bd"), "name" : "user0", "age" : 20 }
{ "_id" : ObjectId("51996ef22b76790566165e47"), "name" : 23, "age" : 33 }
> db.user.find({name:{$type:1}}) # 查找name为双精度的文档
{ "_id" : ObjectId("51996ef22b76790566165e47"), "name" : 23, "age" : 33 }
```
#### 正则表达式

正则的效率都知道的,得一一解析后再查找,所以效率也是很低;

```LANG
> db.user.find({name:/user.*/i}) # 查询name以user开头不区分大小写的文档 

> db.goods.find({goods_name:/诺基亚.*/},{goods_name:1}); # 以诺基亚开头的商品
```
#### 范围查询

小于`$lt`

大于`$gt`

小于或等于`$lte`

大于或等于`$gte`
#### limit

```LANG
> db.user.find({age:{$gte:5}}).limit(3) # 限制返回的是三条数据
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b8"), "name" : "user6", "age" : 6 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b9"), "name" : "user7", "age" : 7 }

```
#### 分页查询

使用到skip 和limit方法.skip表示跳过前面的几个文档,limit表示显示几个文档.

```LANG
> db.user.find()
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 2 }
> db.user.find().skip(2).limit(3) # 跳过前两个文档查询后面的三个文档,经过测试这两个方法的使用顺序没有影响
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 1 }
> db.user.find().limit(3).skip(2)
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 1 }
```
#### sort 排序

在mongodb中排序很简单,使用sort方法,传递给它你想按照哪个字段的哪种方式排序即可.这里1代表升序,-1代表降序.

```LANG
> db.user.find()
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
> db.user.find().sort({age:1})
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
> db.user.find().sort({age:-1})
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 5 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 4 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 3 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
```
#### group 分组查询

mongodb中的group可以实现类似关系型数据库中的分组的功能,但是mongodb中的group远比关系型数据库中的group强大,可以实现map-reduce功能,关于什么是map-reduce,会在后续大数据专题里面说明,这里先略过,感兴趣的朋友可以百度

group中的json参数类似这样{key:{字段:1},initial:{变量:初始值},$reduce:function(doc,prev){函数代码}}.

其中的字段代表,需要按哪个字段分组.
变量表示这一个分组中会使用的变量,并且给一个初始值.可以在后面的$reduce函数中使用.
`$reduce`的两个参数,分别代表当前的文档和上个文档执行完函数后的结果.如下我们按年龄分组,同级不同年龄的用户的多少:

```LANG
> db.user.find()
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 2 }

> db.user.group({key:{age:1},initial:{count:0},$reduce:function(doc,prev){prev.count++}}) 
[
        {
                "age" : 0,
                "count" : 1
        },
        {
                "age" : 1,
                "count" : 3
        },
        {
                "age" : 2,
                "count" : 2
        }
]

> db.user.group({key:{age:1},initial:{users:[]},$reduce:function(doc,prev){prev.users.push(doc.name)}}); #由于内部是使用js引擎来解析的,所以完全可以通过js语法来操作,这使得虽然mongodb的分组很麻烦但却很灵活
[
        {
                "age" : 0,
                "users" : [
                        "user0"
                ]
        },
        {
                "age" : 1,
                "users" : [
                        "user1",
                        "user3",
                        "user4"
                ]
        },
        {
                "age" : 2,
                "users" : [
                        "user2",
                        "user5"
                ]
        }
]

# 另外本函数还有两个可选参数 condition 和 finalize

# condition就是分组的条件筛选类似mysql中的having
> db.user.group({key:{age:1},initial:{users:[]},$reduce:function(doc,prev){prev.users.push(doc.name)},condition:{age:{$gt:0}}}) # 筛选出age大于0的;
[
        {
                "age" : 1,
                "users" : [
                        "user1",
                        "user3",
                        "user4"
                ]
        },
        {
                "age" : 2,
                "users" : [
                        "user2",
                        "user5"
                ]
        }
]
```
#### count 统计

```LANG
> db.goods.count() #不传参数就统计该集合的总数
31
> db.goods.count({cat_id:3}) # 统计cat_id=3的总数
15
```
#### distinct 排重

```LANG
> db.user.find()
{ "_id" : ObjectId("5198c286c686eb50e2c843b2"), "name" : "user0", "age" : 0 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b3"), "name" : "user1", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b4"), "name" : "user2", "age" : 2 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b5"), "name" : "user3", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b6"), "name" : "user4", "age" : 1 }
{ "_id" : ObjectId("5198c286c686eb50e2c843b7"), "name" : "user5", "age" : 2 }
> db.user.distinct("age") # 略微有点特殊,传入的参数直接是字符串,而不是对象;
[ 0, 1, 2 ]
```
#### 子文档查询 $elemMatch与对象中的属性

```LANG
{ _id: 1, results: [ 82, 85, 88 ] }
{ _id: 2, results: [ 75, 88, 89 ] }

db.scores.find(
   { results: { $elemMatch: { $gte: 80, $lt: 85 } } } #查询results文档中的元素同时满足即大于80并且又小于85的,注意此处只要其中一个元素满足这个查询就会返回
)

{ "_id" : 1, "results" : [ 82, 85, 88 ] }
```

```LANG
> db.user.find();
{ "_id" : ObjectId("55c070a02cc8cec37073a1d9"), "name" : "zxg", "age" : 28, "hobby" : { "life" : [ "电影", "小说", "漫画" ], "work" : [ "发呆", "发呆2" ], "home" : "玩耍" } }
{ "_id" : ObjectId("55c070a52cc8cec37073a1da"), "name" : "jyh", "age" : 28, "hobby" : { "life" : [ "卖萌", "养兔子", "做家务" ], "work" : [ "郁闷", "郁闷2" ], "home" : "卖萌" } }
{ "_id" : ObjectId("55c072db2cc8cec37073a1db"), "name" : "jyh", "age" : 28, "hobby" : [ { "life" : [ "卖萌", "养兔子", "做家务" ] }, { "work" : [ "郁闷", "郁闷2" ] }, { "home" : "卖萌" } ] }

> db.user.find({hobby:{$elemMatch:{home:'卖萌'}}}) # 注意上文的结构,必须是要在数组中才可以查出
{ "_id" : ObjectId("55c072db2cc8cec37073a1db"), "name" : "jyh", "age" : 28, "hobby" : [ { "life" : [ "卖萌", "养兔子", "做家务" ] }, { "work" : [ "郁闷", "郁闷2" ] }, { "home" : "卖萌" } ] }

> db.user.find({'hobby.home':'卖萌'}) # 注意,hobby.home类似js中对象与属性的操作方式,但是要加上引号,否则会报错
{ "_id" : ObjectId("55c070a52cc8cec37073a1da"), "name" : "jyh", "age" : 28, "hobby" : { "life" : [ "卖萌", "养兔子", "做家务" ], "work" : [ "郁闷", "郁闷2" ], "home" : "卖萌" } }
{ "_id" : ObjectId("55c072db2cc8cec37073a1db"), "name" : "jyh", "age" : 28, "hobby" : [ { "life" : [ "卖萌", "养兔子", "做家务" ] }, { "work" : [ "郁闷", "郁闷2" ] }, { "home" : "卖萌" } ] }
```
### 查询实例

以下查询基于ecshop网站的数据查询

```LANG
# 本店价格低于或等于100元的商品($lte)
db.goods.find({shop_price:{$lte:100}},{goods_name:1,shop_price:1});

# 取出第4栏目或第11栏目的商品($in)
 db.goods.find({cat_id:{$in:[4,11]}},{goods_name:1,shop_price:1});

# 取出100<=价格<=500的商品($and)
db.goods.find({$and:[{'shop_price':{$gte:100}},{'shop_price':{$lte:500}}]},{_id:0,shop_price:1})

# 取出不属于第3栏目且不属于第11栏目的商品($and $nin和$nor分别实现)
db.goods.find({$and:[{cat_id:{$ne:3}},{cat_id:{$ne:11}}]},{_id:0,cat_id:1})
db.goods.find({cat_id:{$nin:[3,11]}},{_id:0,cat_id:1})
db.goods.find({$nor:[{cat_id:3},{cat_id:11}]},{_id:0,cat_id:1})

# 取出价格大于100且小于300,或者大于2000且小于5000的商品()
db.goods.find({$or:[{$and:[{shop_price:{$gt:100}}, {shop_price:{$lt:300} }]}, {$and:[{shop_price:{$gt:2000}}, {shop_price:{$lt:5000} }] } ] },{_id:0,shop_price:1} )

# 取出所有goods_id为偶数的商品;
db.goods.find({goods_id:{$mod:[2,0]}},{_id:0,goods_id:1})
```
