# [MongoDB学习笔记——分片（Sharding）][0] 

## 分片（ Sharding ）

 分片就是将数据进行拆分，并将其分别存储在不同的服务器上 MongoDB 支持自动分片能够自动处理数据在分片上的分布

###  MongoDB 分片有三种角色

* 配置服务器：一个单独的 mongod 进程，主要记录了哪个分片服务器包含了哪些数据的信息，保存的只是数据的分布表，如果配置服务器不可用时，将变为只读，不能进行分片和数据迁移，  配置服务器的 1KB 空间相当于真实数据的 200MB ，所以配置服务器不需要太多的资源和配置。但是每个配置服务器都建议部署在不同的物理机上，  配置服务器相当于整个集群的大脑保存了集群和分片的元数据，并且 mongos 路由服务器需要从配置服务器获取配置信息，  因此应该首先建立配置服务器，并且启用日志功能。自 3.2 开始配置服务器可以部署为副本集。  使用副本集作为配置服务器时要满足以下条件：

    * 使用的副本集的配置服务器分片集群要超过 3 配置服务器，因为副本集可以有最多 50 个成员
    * 要将配置服务器部署为副本集，配置服务器必须运行 WiredTiger 存储引擎
    * 配置服务器部署为副本集时必须要没有仲裁者
    * 配置服务器为副本集时不能存在延迟节点 slaveDelay 不能为 0
    * 必须建立的索引  （即没有成员应该有 buildIndexes 设置为 false ）

* 路由服务器： mongos 进程，起到一个前端路由的功能，供客户端进行接入，本身不会保存数据，在启动时从配置服务器加载集群信息，所以配置路由服务器时，不需要指定数据目录，开启 mongos 进程需要知道配置服务器的地址，指定 configdb 选项。  当客户端连接到路由服务器时，会询问配置服务器需要到哪个分片服务器上查询或保存记录，然后再链接相应的 Shard 进行操作，最后将结果整合给返回给客户端
* 分片服务器：可以是一个副本集或单独的 mongod 进程，保存分片后的集合数据

### 块 (chunk)

 MongoDB 将数据拆分为 chunk ，每个 chunk 都是 collection 中的一段连续的数据记录，为防止一个 chunk 变的越来越大，当一个 chunk 增加到特定大小时，会被自动拆分为两个较小的 chunk 。默认块大小为 64M 新的分片集合刚开始只有一个 chunk ，所有文档都位于这个 chunk 中，块的范围是 $minKey 至 $maxKey 。随着数据的增加会被拆分为多个块，块的范围也会逐步被调整，多使用 [a,b) 表示区间范围

### 均衡器 (balancer)

 均衡器周期性的检查每个分片之间的数据是否存在不均衡情况，如果存在，就会进行块的迁移。

### 片键

 对集合进行分片时，需要选择一个或多个字段用于数据拆分。  拆分数据最常用的数据分发方式有三种，升序片键，随机分发的片键，小基数片键。

* 升序片键：  升序片键会导致所有数据总是被添加到最后一个数据块中，导致存在一个单一不可分散的人点，用一个分片承担了所有的读写。
* 随机分发片键：  这种分片虽然可以得到一组均匀分布于各分片的数据块。但是考虑到数据序列的随机性，一般情况下这个数据不会被加载到内存中，所以此时的 MongoDB 会引发大量的磁盘 IO 给 RAM 带来更大压力，并且由于片键必须有索引，所以如果选择了不依据它进行查询的随机键，基本浪费了一个索引，导致 MongoDB 写操作变慢。
* 小基数片键：  即片键值个数有限的键，这种片键容易导致块的个数有限，并且导致块的体积越来越大。

建议使用准升序键加搜索键的组合片键  升序片键最好能够对应 N 多个数据块，搜索键则应当是通常用来查询的字段，搜索键不能是一个升序字段，这样会把片键降级为一个升序片键，应该具有非升序，分布随即，且基数适当的特点

 注意：

* 片键上必须有索引，因此如果选择了从不依据索引查询的随机键，基本上可以说浪费了一个索引，另一方面索引的增加会降低写操作的速度，所以降低索引量也是非常必要的。
* 片键不可以是数组
* 应该选择不会被改变或很少发生变动的字段作为片键，因为文档一旦插入，正则片键值就无法在进行修改了，如果要修改则必须先删除在进行修改

### 部署分片集群

 1. 创建配置服务器：
```
 // config 1

 systemLog:

 path: D:\mongodb\sharding\config\c0\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\config\c0\data

 net:

 port: 27020

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\config\c0\key

 sharding:

 clusterRole: configsvr

 // config 2

 systemLog:

 path: D:\mongodb\sharding\config\c1\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\config\c1\data

 net:

 port: 27021

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\config\c1\key

 sharding:

 clusterRole: configsvr

 //config 3

 systemLog:

 path: D:\mongodb\sharding\config\c2\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\config\c2\data

 net:

 port: 27022

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\config\c2\key

 sharding:

 clusterRole: configsvr
```
 启动以上三个配置服务器
```
 mongod -config D:\mongodb\sharding\config\c0\mongodb.conf

 mongod -config D:\mongodb\sharding\config\c1\mongodb.conf

 mongod -config D:\mongodb\sharding\config\c2\mongodb.conf
```
 配置路由服务器
```
 //route 1

 systemLog:

 path: D:\mongodb\sharding\route\r0\logs\mongodb.log

 logAppend: true

 destination: file

 net:

 port: 27020

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\route\r0\key

 sharding:

 autoSplit: true

 configDB: 127.0.0.1:27010,127.0.0.1:27011,127.0.0.1:27012

 chunkSize: 64

 //route 2

 systemLog:

 path: D:\mongodb\sharding\route\r1\logs\mongodb.log

 logAppend: true

 destination: file

 net:

 port: 27021

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\route\r1\key

 sharding:

 autoSplit: true

 configDB: 127.0.0.1:27010,127.0.0.1:27011,127.0.0.1:27012

 chunkSize: 64
```
 启动以上两个路由服务器
```
 mongos -config D:\mongodb\sharding\route\r1\mongodb.conf

 mongos -config D:\mongodb\sharding\route\r1\mongodb.conf
```
 配置分片服务器
```
 //shard 1

 systemLog:

 path: D:\mongodb\sharding\shards\s0\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\shards\s0\data

 net:

 port: 27030

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\shards\s0\key

 sharding:

 clusterRole: shardsvr

 //shard 2

 systemLog:

 path: D:\mongodb\sharding\shards\s1\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\shards\s1\data

 net:

 port: 27031

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\shards\s1\key

 sharding:

 clusterRole: shardsvr
```
 启动分片服务器
```
 mongod -config D:\mongodb\sharding\shards\s0\mongodb.conf

 mongod -config D:\mongodb\sharding\shards\s1\mongodb.conf
```

登录到 mongos ，添加 shard 分片

     mongo --port 27021

 添加分片服务器

    db.runCommand({addshard:"127.0.0.1:27030"})
    sh.addShard("127.0.0.1:27031")

 配置分片存储的数据库  `sh.enableSharding(dbname)` dbname- 数据库名称

    sh.enableSharding("testSharding")

 设置分片集合的名称及指定片键  `sh.shardCollection(fullName,key,unique)` fullname-dbname.collectionname 数据库名称 + 集合名称； key- 片键； unique- 默认为 true ，为 true 时在基础索引上创建唯一约束

     sh.shardCollection("testSharding.users",{userName:1})

 创建测试数据
```
 use testSharding
 for (i = 5000; i < 100000; i++) {
     db.users.insert({
         "i": i,
         "userName": "user" + i,
         "age": Math.floor(Math.random() * 120),
         "created": new Date(),
         total: Math.floor(Math.random() * 100) * i
     })
 }
```
 创建分片 3 为副本集
```
 //shardRepliSet1

 systemLog:

 path: D:\mongodb\sharding\shards\s2\rs0\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\shards\s2\rs0\data

 net:

 port: 27035

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\shards\s2\rs0\key

 replication:

 replSetName: replcaSetTest

 secondaryIndexPrefetch: all

 sharding:

 clusterRole: shardsvr

 //shardRepliSet2

 systemLog:

 path: D:\mongodb\sharding\shards\s2\rs1\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\shards\s2\rs1\data

 net:

 port: 27032

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\shards\s2\rs1\key

 replication:

 replSetName: replcaSetTest

 secondaryIndexPrefetch: all

 sharding:

 clusterRole: shardsvr

 //shardRepliSet3

 systemLog:

 path: D:\mongodb\sharding\shards\s2\rs2\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\shards\s2\rs2\data

 net:

 port: 27033

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\shards\s2\rs2\key

 replication:

 replSetName: replcaSetTest

 secondaryIndexPrefetch: all

 sharding:

 clusterRole: shardsvr

 //shardRepliSet4

 systemLog:

 path: D:\mongodb\sharding\shards\s2\rs3\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\sharding\shards\s2\rs3\data

 net:

 port: 27038

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\sharding\shards\s2\rs3\key

 replication:

 replSetName: replcaSetTest

 secondaryIndexPrefetch: all

 sharding:

 clusterRole: shardsvr
```
 启动副本集实例
```
 mongod -config D:\mongodb\sharding\shards\s2\rs0\mongodb.conf

 mongod -config D:\mongodb\sharding\shards\s2\rs1\mongodb.conf

 mongod -config D:\mongodb\sharding\shards\s2\rs2\mongodb.conf

 mongod -config D:\mongodb\sharding\shards\s2\rs3\mongodb.conf
```
 配置及初始化副本集
```
 rsConfig = {
     _id: "replcaSetTest",
     members: [
         { _id: 0, host: "127.0.0.1:27032" },
         { _id: 1, host: "127.0.0.1:27033" },
         { _id: 2, host: "127.0.0.1:27035" },
         { _id: 3, host: "127.0.0.1:27038" }
     ]
 };
 rs.initiate(rsConfig);
```
 添加副本集分片至分片配置

     sh.addShard("replcaSetTest/127.0.0.1:27032,127.0.0.1:27033,127.0.0.1:27035,127.0.0.1:27038")

 设置配置服务器为副本集  配置文件中添加节点 `clusterRole: configsvr`  即可，其它请参考副本集配置。

 `db.users.stats()` 查看集合 `users` 分片信息

### 管理维护分片集群

 列出所有的分片服务器

    use admin
    db.runCommand({listshards:1})

 查看集群摘要信息

     sh.status();

 查看分片集群信息

     printShardingStatus()

[0]: http://www.cnblogs.com/AlvinLee/p/6117091.html