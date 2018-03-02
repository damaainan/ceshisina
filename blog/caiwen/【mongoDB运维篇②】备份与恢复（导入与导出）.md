## 【mongoDB运维篇②】备份与恢复(导入与导出)

来源：[https://segmentfault.com/a/1190000004263279](https://segmentfault.com/a/1190000004263279)

导入/导出可以操作的是本地的mongodb服务器,也可以是远程的服务器
所以,都有如下通用选项:

```LANG
-h host   主机
--port port    端口
-u username 用户名
-p passwd   密码

```
## mongoexport 导出

```LANG
-d  库名
-c  表名
-f  field1,field2...列名
-q  查询条件
-o  导出的文件名
--type=csv  导出csv格式(便于和传统数据库交换数据)

```

```LANG
# 导出shop库下面的goods表
./mongoexport -d shop -c goods -o goods.json
2015-08-20T18:12:05.693+0800    connected to: localhost #从哪里导出
2015-08-20T18:12:05.697+0800    exported 31 records   # 导出的文档数

# 导出shop库下面的goods表中的goods_id,goods_name列
./mongoexport -d shop -c goods -f goods_id,goods_name -o goods_id_name.json

# 只导出价格低于1000元的行
./mongoexport -d shop -c goods -f goods_id,goods_name,shop_price -q '{shop_price:{$lt:200}}' -o goodslt100.json

# 导出shop库下面的goods表中的goods_id,goods_name列
./mongoexport -d shop -c goods -f goods_id,goods_name -o ./goods_id_name.csv --type=csv 
```

注意只能是导出数据,不包括相关的索引信息

详情请参考: [http://docs.mongodb.org/v3.0/reference/program/mongoexport/#bin.mongoexport][0]
## mongoimport 导入

```LANG
-d 待导入的数据库
-c 待导入的表(不存在会自己创建)
--type  csv/json(默认)
--file 备份文件路径

```

```LANG
# 导入json
./mongoimport -d shop -c goodslt100 --file ./goodslt100.json

# 导入csv,必须要指定fields
./bin/mongoimport -d test -c goods_csv --type csv -f goods_id,goods_name --file ./goodsall.csv 
```

以上的导出,仅仅是导出数据,相关的索引信息没有被导出;

二进制备份,不仅可以备份数据,还可以备份索引, 
备份数据比较小.

参考: [http://docs.mongodb.org/v3.0/reference/program/mongoimport/#bin.mongoimport][1]
## mongodump 导出二进制bson结构的数据及其索引信息

```LANG
-d  库名
-c  表名
-f  field1,field2...列名

mongodump -d test  [-c 表名]  默认是导出到mongo下的dump目录

```

* 导出的文件放在以database命名的目录下

* 每个表导出2个文件,分别是bson结构的数据文件, json的索引信息

* 如果不声明表名, 导出所有的表



```LANG
mongodump -d shop
```

参考: [http://docs.mongodb.org/v3.0/reference/program/mongodump/#bin.mongodump][2]
## mongorestore 导入二进制文件

```LANG
mongorestore -h IP --port 端口 -u 用户名 -p 密码 -d 数据库 --drop 文件存在路径
--drop的意思是，先删除所有的记录，然后恢复

```

```LANG
 ./mongorestore -d goods_bson ./dump/shop/ 3.0版本去掉了--directoryperdb
```

参考: [http://docs.mongodb.org/v3.0/reference/program/mongorestore/#bin.mongorestore][3]

[0]: http://docs.mongodb.org/v3.0/reference/program/mongoexport/#bin.mongoexport
[1]: http://docs.mongodb.org/v3.0/reference/program/mongoimport/#bin.mongoimport
[2]: http://docs.mongodb.org/v3.0/reference/program/mongodump/#bin.mongodump
[3]: http://docs.mongodb.org/v3.0/reference/program/mongorestore/#bin.mongorestore