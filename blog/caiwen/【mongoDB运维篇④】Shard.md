## 【mongoDB运维篇④】Shard 分片集群

来源：[https://segmentfault.com/a/1190000004263332](https://segmentfault.com/a/1190000004263332)


## 简述
### 为何要分片

* 减少单机请求数，降低单机负载，提高总负载

* 减少单机的存储空间，提高总存空间。




![][0]
### **`常见的mongodb sharding 服务器架构`** 


![][1]
要构建一个 MongoDB Sharding Cluster，需要三种角色：

**`Shard Server`** 
即存储实际数据的分片，每个Shard可以是一个mongod实例，也可以是一组mongod实例构成的Replication Set。为了实现每个Shard内部的auto-failover(自动故障切换)，MongoDB官方建议每个Shard为一组Replica Set。

**`Config Server`** 
为了将一个特定的collection存储在多个shard中，需要为该collection指定一个shard key(片键)，例如{age: 1} ，shard key可以决定该条记录属于哪个chunk(分片是以chunk为单位,后续会介绍)。Config Servers就是用来存储：所有shard节点的配置信息、每个chunk的shard key范围、chunk在各shard的分布情况、该集群中所有DB和collection的sharding配置信息。

**`Route Process`** 
这是一个前端路由，客户端由此接入，然后询问Config Servers需要到哪个Shard上查询或保存记录，再连接相应的Shard进行操作，最后将结果返回给客户端。客户端只需要将原本发给mongod的查询或更新请求原封不动地发给Routing Process，而不必关心所操作的记录存储在哪个Shard上。（所有操作在mongos上操作即可）
## 配置分片服务器

下面我们在同一台物理机器上构建一个简单的 Sharding Cluster：


![][2]

```LANG
Shard Server 1：27017
Shard Server 2：27018
Config Server ：27027
Route Process：40000

```
### 步骤一: 启动Shard Server

```LANG
mkdir -p ./data/shard/s0 ./data/shard/s1  #创建数据目录
mkdir -p ./data/shard/log # 创建日志目录

./bin/mongod --port 27017 --dbpath /usr/local/mongodb/data/shard/s0 --fork --logpath /usr/local/mongodb/data/shard/log/s0.log # 启动Shard Server实例1

./bin/mongod --port 27018 --dbpath /usr/local/mongodb/data/shard/s1 --fork --logpath /usr/local/mongodb/data/shard/log/s1.log # 启动Shard Server实例2
```
### 步骤二: 启动Config Server

```LANG
mkdir -p ./data/shard/config #创建数据目录

./bin/mongod --port 27027 --dbpath /usr/local/mongodb/data/shard/config --fork --logpath /usr/local/mongodb/data/shard/log/config.log #启动Config Server实例
```

注意，这里我们完全可以像启动普通mongodb服务一样启动，不需要添加—shardsvr和configsvr参数。因为这两个参数的作用就是改变启动端口的，所以我们自行指定了端口就可以

### 步骤三: 启动Route Process

```LANG
./bin/mongos --port 4000 --configdb localhost:27027 --fork --logpath /usr/local/mongodb/data/shard/log/route.log --chunkSize=1 # 启动Route Server实例
```

mongos启动参数中，chunkSize这一项是用来指定chunk的大小的，单位是MB，默认大小为200MB，为了方便测试Sharding效果，我们把chunkSize指定为 1MB。意思是当这个分片中插入的数据大于1M时开始进行数据转移
### 步骤四: 配置Sharding

```LANG
# 我们使用MongoDB Shell登录到mongos，添加Shard节点
./bin/mongo admin --port 40000 #此操作需要连接admin库
> db.runCommand({ addshard:"localhost:27017" }) #添加 Shard Server 或者用 sh.addshard()命令来添加,下同;
{ "shardAdded" : "shard0000", "ok" : 1 }
> db.runCommand({ addshard:"localhost:27018" })
{ "shardAdded" : "shard0001", "ok" : 1 }

> db.runCommand({ enablesharding:"test" }) #设置分片存储的数据库
{ "ok" : 1 }

> db.runCommand({ shardcollection: "test.users", key: { id:1 }}) # 设置分片的集合名称。且必须指定Shard Key，系统会自动创建索引，然后根据这个shard Key来计算
{ "collectionsharded" : "test.users", "ok" : 1 }

 > sh.status(); #查看片的状态
 > printShardingStatus(db.getSisterDB("config"),1); # 查看片状态(完整版);
 > db.stats(); # 查看所有的分片服务器状态
 
```

注意这里我们要注意片键的选择，选择片键时需要根据具体业务的数据形态来选择，切不可随意选择，实际中尤其不要轻易选择自增_id作为片键，除非你很清楚你这么做的目的，具体原因我不在此分析，根据经验推荐一种较合理的片键方式，“自增字段+查询字段”，没错，片键可以是多个字段的组合。

另外这里说明一点，分片的机制：mongodb不是从单篇文档的级别,绝对平均的散落在各个片上, 而是N篇文档,形成一个块"chunk",优先放在某个片上, 当这片上的chunk,比另一个片的chunk区别比较大时(>=3) ,会把本片上的chunk,移到另一个片上, 以chunk为单位,维护片之间的数据均衡。

也就是说，一开始插入数据时，数据是只插入到其中一块分片上的，插入完毕后，mongodb内部开始在各片之间进行数据的移动，这个过程可能不是立即的，mongodb足够智能会根据当前负载决定是立即进行移动还是稍后移动。
在插入数据后，立马执行db.users.stats();两次可以验证如上所说。

这种分片机制,节省了人工维护成本,但是由于其是优先往某个片上插入,等到chunk失衡时,再移动chunk,并且随着数据的增多,shard的实例之间,有chunk来回移动的现象,这将会为服务器带来很大的IO开销,解决这种开销的方法,就是手动预先分片;
## 手动预先分片

以shop.user表为例

```LANG
sh.shardCollection(‘shop.user’,{userid:1}); # user表用userid做shard key

for(var i=1;i<=40;i++) { sh.splitAt('shop.user',{userid:i*1000}) } # 预先在1K 2K...40K这样的界限切好chunk(虽然chunk是空的), 这些chunk将会均匀移动到各片上.
```

通过mongos添加user数据. 数据会添加到预先分配好的chunk上, chunk就不会来回移动了.
## repliction set and shard

一般mongoDB如果真的到了分片的级别后,那片服务器避无可免的要用到复制集,部署的基本思路同上,只需要注意两点:

```LANG
sh.addShard( host ) server:port OR setname/server:port # 如果是复制集的片服务器,我们应该复制集的名称写在前面比如
sh.addShard('ras/192.168.42.168:27017'); # 27017也就是复制集中的primary
```

另外在启动本机的mongod服务的时候,最好把ip也给写进去,否则有可能会有不可预知的错误;

[0]: http://static.oschina.net/uploads/space/2014/0201/102152_ETk2_247956.png
[1]: http://static.oschina.net/uploads/space/2014/0201/102312_Pyve_247956.png
[2]: http://dl.iteye.com/upload/attachment/0071/8416/b4f892a0-bd66-31cd-ac1c-d453c9cda169.gif