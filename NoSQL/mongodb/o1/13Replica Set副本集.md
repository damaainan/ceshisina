# [MongoDB学习笔记——Replica Set副本集][0] 

### 副本集

 可以将 MongoDB 中的副本集看作一组服务器集群由一个主节点和多个副本节点等组成，相对于之前讲到的主从复制提供了故障自动转移的功能

#### 副本集实现数据同步的方式依赖于 local 数据库中的 oplog 数据

* oplog 是存在与主节点或副本节点上的 local 数据库中的一个固定集合，记录了每一次主节点的写操作，或副本节点每一次从主节点复制数据的操作
* 备份节点通过定时从主节点获取 oplog 数据，并在本机执行这些操作来实现主从复制的功能，同样的每个副本节点也可以作为数据源给其他成员使用
* 备份节点在本机上执行完从主节点获取到的 oplog 数据后，会在自己的 local 数据库中记录这些的操作
* 如果某个副本节点由于某些原因宕机了，当重启后会从 oplog 中最后一个操作开始进行同步，由于复制过程是先进行复制再写入本机 oplog 的，所以副本节点可能会在已经同步过的数据上再次执行复制操作，不过 oplog 中的统一操作执行多次与执行一次效果是一样的

#### oplog 字段说明

* ts: 执行某个操作的时间戳
* op: 操作类型（ i-insert;d-delete;u-update ）
* ns: 操作的集合的名称
* o:document 的内容

#### 副本集中的各种角色

* 主节点 —— 有且只有一个，默认处理了所有的客户端请求（读和写）
* 副本节点 —— 可以有多个，保存主节点的副本数据，当主节点出问题时可以通过投票升级为主节点，可以处理客户端的只读请求
* 仲裁节点 —— 不进行数据复制存储，也不为客户端提供服务，唯一的作用是进行故障转移时的投票，选举出新的主节点  仲裁节点一旦被设置则永远不能成为非仲裁节点，反之一样  一个副本集集群中最多只能有一个仲裁节点，如果节点总数为基数时就不需要配置仲裁节点了，因为仲裁节点的作用就是在节点总数为偶数时，如果一半投票给 A ，一半头片给 B 这时仲裁节点就持有关键性的一票  如果有可能尽量使用奇数的集群节点，不要使用仲裁节点
* 隐藏节点 —— 不为客户端提供服务，永远不会成为主节点但是会参与投票，进行数据的复制存储，一般用作数据备份  如果要设置一个节点为隐藏设置其 hidden 属性为 true 即可  只有优先级 priority=0 的节点才能被设置为隐藏
* 延迟同步节点 —— 一般会人为设置一个延迟时间（单位秒）来从主节点同步数据，主要就是用于数据的备份，当数据库发生毁灭性灾难时进行数据恢复  延迟同步节点的优先级 priority=0 并且是隐藏节点，避免读请求被路由到延迟同步节点上  通过属性 slaveDelay （单位秒）来设置一个节点的延迟同步时间
* Secondary-Only—— 通常将性能不高的节点设置为此类型，防止其成为主节点，优先级 priority=0
* Non-Voting—— 没有投票权的副本节点，纯粹用于数据备份，一般只有当节点总数超过 12 个时才会被使用，通过 votes=0 设置其为 Non-Voting 节点

#### 部署副本集

 创建主从 key 文件，用于表示集群私钥的完整路径，如果各个实例的密钥不一致则程序不能正常使用

     openssl rand -base64 100 > key

 创建数据库目录及日志目录并修改其配置文件
```
//replica set 0
systemLog:
path: D:\mongodb\replicaSet\rs0\logs\mongodb.log
logAppend: true
destination: file
storage:
dbPath: D:\mongodb\replicaSet\rs0\data
net:
port: 27017
bindIp: 127.0.0.1
security:
keyFile: D:\mongodb\replicaSet\rs0\key
replication:
replSetName: replcaSetTest
secondaryIndexPrefetch: all

//relica set 1
systemLog:
path: D:\mongodb\replicaSet\rs1\logs\mongodb.log
logAppend: true
destination: file
storage:
dbPath: D:\mongodb\replicaSet\rs1\data
net:
port: 27018
bindIp: 127.0.0.1
security:
keyFile: D:\mongodb\replicaSet\rs1\key
replication:
replSetName: replcaSetTest
secondaryIndexPrefetch: all

//relica set 2
systemLog:
path: D:\mongodb\replicaSet\rs2\logs\mongodb.log
logAppend: true
destination: file
storage:
dbPath: D:\mongodb\replicaSet\rs2\data
net:
port: 27019
bindIp: 127.0.0.1
security:
keyFile: D:\mongodb\replicaSet\rs2\key
replication:
replSetName: replcaSetTest
secondaryIndexPrefetch: all
```
 启动以上三个实例
```
 mongod -config D:\mongodb\replicaSet\rs0\mongodb.conf

 mongod -config D:\mongodb\replicaSet\rs1\mongodb.conf

 mongod -config D:\mongodb\replicaSet\rs2\mongodb.conf
```
 配置及初始化副本集
```
 rsConfig = {

     _id: "replcaSetTest",

     members: [
         { _id: 0, host: "127.0.0.1:27017" },

         { _id: 1, host: "127.0.0.1:27018" },

         { _id: 2, host: "127.0.0.1:27019" }

     ]

 };

 rs.initiate(rsConfig);
```
 _id 就是启动 MongoDB 实例时设置的副本集名称，一定要保障两处副本集名称一致 members 副本集成员数组 , 一个唯一的数值类型的 _id, 一个主机名 rs.initiate() 函数用来初始化副本集

 输入 rs.config() 查询配置修改是否成功，竟然提示没有权限
```
 rs.config()

 2016-11-23T17:18:00.689+0800 E QUERY [thread1] Error: Could not retrieve replica set config: {

 "ok" : 0,

 "errmsg" : "not authorized on admin to execute command { replSetGetConfig: 1.0 }",

 "code" : 13

 }
```
 执行以下名称创建创建管理员账户，并授予权限，创建成功后执行 db.auth()  命令进行登录授权
```
 db.createUser({

     user: 'admin',

     pwd: 'mongodb_123',

     roles: [
         { "role": "clusterAdmin", "db": "admin" },

         { "role": "userAdminAnyDatabase", "db": "admin" },

         { "role": "dbAdminAnyDatabase", "db": "admin" },

         { role: "root", db: "admin" }

     ]

 })

 db.auth("admin","mongodb_123")
```
 再次执行 rs.config() 得到以下信息
```
 rs.config()
 {
     "_id" : "replcaSetTest",
     "version" : 1,
     "protocolVersion" : NumberLong(1),
     "members" : [
         {
         "_id" : 0,
         "host" : "127.0.0.1:27017",
         "arbiterOnly" : false,
         "buildIndexes" : true,
         "hidden" : false,
         "priority" : 1,
         "tags" : {
         },
         "slaveDelay" : NumberLong(0),
         "votes" : 1
     },
     {
         "_id" : 1,
         "host" : "127.0.0.1:27018",
         "arbiterOnly" : false,
         "buildIndexes" : true,
         "hidden" : false,
         "priority" : 1,
         "tags" : {
         },
         "slaveDelay" : NumberLong(0),
         "votes" : 1
         },
     {
         "_id" : 2,
         "host" : "127.0.0.1:27019",
         "arbiterOnly" : false,
         "buildIndexes" : true,
         "hidden" : false,
         "priority" : 1,
         "tags" : {
         },
         "slaveDelay" : NumberLong(0),
         "votes" : 1
     }
     ],

     "settings" : {
         "chainingAllowed" : true,
         "heartbeatIntervalMillis" : 2000,
         "heartbeatTimeoutSecs" : 10,
         "electionTimeoutMillis" : 10000,
         "getLastErrorModes" : {
         },
         "getLastErrorDefaults" : {
             "w" : 1,
             "wtimeout" : 0
         },
         "replicaSetId" : ObjectId("5835597e61b9c2f7c562b9b0")
     }
 }
```
 version 在每次修改副本集配置时都会递增，初始值为 1 `rs.starus()` 或 `rs.isMaster()`  指令可以查询当前副本集的状态

 local 数据库中不仅有 oplog 日志集合，还有一个用于记录主从配置信息的集 ——system.replset 通过这个集合可以查看副本集的配置信息当然在每个实例上执行 rs.config() 或 rs.conf() 也能达到同样的效果

    db.system.replset.find()

 在主节点上创建测试数据
```
 use myReplSetTest
 for (i = 5000; i < 100000; i++) {
     db.users.insert({
         "i": i,
         "userName": "user" + i,
         "age": Math.floor(Math.random() * 120),
         "created": new Date(),
         total: Math.floor(Math.random() * 100) * i
     })
 }

 db.users.find()
```
 在主节点上执行查询 OK ，但是当在副本节点进行执行是则报错 , 提示不是主节点并且 slaveOk=false 不能执行查询操作

     QUERY [thread1] Error: listCollections failed: { "ok" : 0, "errmsg" : "not master and slaveOk=false", "code" : 13435 }

 在副本节点中执行名称 `db.setSlaveOk()` 让副本节点可以读取数据，分单主库压力达到读写分离的效果，再次执行 `db.users.find()` 就正常了

### 副本集管理

 `rs.add()` 可以添加节点  添加节点的方式有两种：   
 1. 通过 oplog 直接添加，这种方式添加节点过程中不需要过多的人工干预就可以完成，但是如果频繁的插入修改等操作会导致 oplog 数据较大，在进行复制时会使源节点的压力较大  并且 oplog 是固定集合，因为他的特性可能会导致复制的数据不一致   
 2. 通过数据库快照（ --fastsync ）获取某一个副本节点的物理文件来做初始化数据，剩余的部分通过 oplog 日志进行追加可以解决上述的问题，

 通过方式 1 来新增节点，方式同创建 mongodb 副本集（创建对应的数据文件夹，修改配置文件，启动 mongodb 实例）
```
 systemLog:

 path: D:\mongodb\replicaSet\rs3\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\replicaSet\rs3\data

 net:

 port: 27020

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\replicaSet\rs3\key

 replication:

 replSetName: replcaSetTest

 secondaryIndexPrefetch: all

 mongod -config D:\mongodb\replicaSet\rs3\mongodb.conf

 // 主节点中执行

 rs.add("127.0.0.1:27020");
```
 使用方式 2 进行节点新增   
 1. 复制 rs3 中文件至文件夹 rs4   
 2. 修改配置文件如下：   
 3. 启动新的 mongodb 实例  
```
 systemLog:

 path: D:\mongodb\replicaSet\rs4\logs\mongodb.log

 logAppend: true

 destination: file

 storage:

 dbPath: D:\mongodb\replicaSet\rs4\data

 net:

 port: 27021

 bindIp: 127.0.0.1

 security:

 keyFile: D:\mongodb\replicaSet\rs4\key

 replication:

 replSetName: replcaSetTest

 secondaryIndexPrefetch: all

 mongod -config D:\mongodb\replicaSet\rs3\mongodb.conf

 // 主节点中添加测试数据

 db.usertest.insert({name:1})

 // 主节点中执行

 rs.add("127.0.0.1:27021");

 // 新添加的副本节点中执行 show collections 发现已经同步
```

`rs.remove()` 可以进行节点移除

     rs.remove("127.0.0.1:27020");

 `rs.reconfig()` 可以修改副本集配置 , 特别是针对复杂的副本集配置，比 `rs.add()` 和 `rs.remove()` 更有效

     config = rs.config();

     config.members[1].priority = 20;

     rs.reconfig(config);

 以上命令修改了 127.0.0.1:27018 的优先级，发现修改完成后自动切换为了主节点，通过命令 `rs.status()` 可以查看

#### 副本集监控

 主节点中执行  `db.printReplicationInfo()`  命令可以查看 oplog 信息

* configured oplog size—— 配置的 oplog 文件大小
* log length start to end——oplog 日志的启用时间段
* oplog first event time—— 第一个事务日志产生的时间
* oplog last event time—— 最后一个事务日志产生的时间
* now—— 当前时间

副本节点中执行 `db.printSlaveReplicationInfo()` 命令可以查看同步状态信息

* source—— 从库的 IP 及端口
* syncedTo—— 当前的同步情况，，延迟了多久等信息

[0]: http://www.cnblogs.com/AlvinLee/p/6096620.html