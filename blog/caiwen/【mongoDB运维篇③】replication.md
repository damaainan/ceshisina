## 【mongoDB运维篇③】replication set复制集

来源：[https://segmentfault.com/a/1190000004263290](https://segmentfault.com/a/1190000004263290)


## 介绍

replicattion set 多台服务器维护相同的数据副本,提高服务器的可用性,总结下来有以下好处:

* 数据备份与恢复

* 读写分离



### MongoDB 复制集的结构以及基本概念


![][0]
正如上图所示，MongoDB 复制集的架构中，主要分为两部分：主节点（Primary）和从节点（Secondary）。

**`主节点`** ：在一个复制集中只有并且必须有一个主节点，主节点也是众多实例中 **`唯一可以接收客户端写操作`** 的节点，当然也可以进行读操作；

**`从节点`** ：从节点会复制主节点的操作，以获取完全一致的数据集。客户端不能够直接对从节点进行写操作，但是可以进行读操作，这个需要通过复制集选项进行设置。

**`投票节点`** ：投票节点 并不含有 复制集中的数据集副本，且也 无法 升职为主节点。投票节点的存在是为了使复制集中的节点数量为奇数，这样保证在进行投票的时候不会出现票数相同的情况。如果添加了一个节点后，总节点数为偶数，那么就需要相应的增加一个投票节点。

注：MongoDB 3.0 把复制集中的成员数量从原来的12个提升到了50个，但是投票节点的数量仍然保持不变，还是7个。

### 最基本的复制集架构


![][1]
 **`一个主节点，两个从节点，自动化故障切换的特性`** 
最基本的复制集架构是有3个节点的形式。这样在主节点不可用以后，从节点会进行投票选出一个节点成为主节点，继续工作。如下图所示：


![][2]
#### **`重新投票选出主节点`** 

三个节点的复制集架构，还有另外一种形式：一个主节点，一个从节点，一个投票节点。如下图所示：


![][3]
 **`一个主节点，一个从节点，一个投票节点`** 
在这种架构中，当主节点不可用时，只有从节点可以升为主节点，而投票节点是不可以成为主节点的。投票节点仅仅在选举中进行投票。如下图所示：


![][4]
### **`从节点无法升职为主节点的情况`** 

**`其他概念`** 
 从节点还有集中特殊的设置情况，不同的设置有不同的需求：

**`优先级为0`** ：设置 priority:0 ，那么该结点将不能成为主节点，但是其数据仍是与主节点保持一致的,而且应用程序也可以进行读操作。这样可以在某些特殊的情况下，保证其他特定节点优先成为主节点。


![][5]

**`隐藏节点`** ：隐藏节点与主节点的数据集一致，但是对于应用程序来说是不可见的。隐藏节点可以很好的与 复制集 中的其他节点隔离，并应对特殊的需求，比如进行报表或者数据备份。隐藏节点也应该是一个 **`不能升职为主节点的优先级为0`** 的节点。


![][6]

**`延时节点`** ：延时节点也将从 复制集 中主节点复制数据，然而延时节点中的数据集将会比复制集中主节点的数据延后。举个例子，现在是09：52，如果延时节点延后了1小时，那么延时节点的数据集中将不会有08：52之后的操作。

由于延时节点的数据集是延时的，因此它可以帮助我们在人为误操作或是其他意外情况下恢复数据。举个例子，当应用升级失败，或是误操作删除了表和数据库时，我们可以通过延时节点进行数据恢复。

**`oplog`** ：全拼 oprations log，它保存有数据库的所有的操作的记录。在复制集中，主节点产生 oplog，然后从节点复制主节点的 oplog 进行相应的操作，这样达到保持数据集一致的要求。因此从节点的数据与主节点的数据相比是有延迟的。
## 配置

```LANG
# 创建数据存储目录
mkdir -p /data/r0 /data/r1 /data/r2

# 创建日志文件
touch /var/log/mongo17.log /var/log/mongo18.log /var/log/mongo19.log

#启动3个实例,且声明实例属于某复制集 rsa
./bin/mongod --port 27017 --dbpath /data/r0 --smallfiles --replSet rsa --fork --logpath /var/log/mongo17.log
./bin/mongod --port 27018 --dbpath /data/r1 --smallfiles --replSet rsa --fork --logpath /var/log/mongo18.log
./bin/mongod --port 27019 --dbpath /data/r2 --smallfiles --replSet rsa --fork --logpath /var/log/mongo19.log

# 进入27017进行配置初始化
./bin/mongo --port 27017
rsconf = {
    _id:'rsa',
    members:
    [
        {_id:0,
        host:'192.168.42.168:27017'
        }
    ]
}
rs.initiate(rsconf); # 如果以后需要再重载一下config的话,用rs.reconfig(rsconf);

# 添加节点
rs.add('192.168.42.168:27018');
rs.add('192.168.42.168:27019');

# 查看状态
rs.status();

# 删除节点
rs.remove('192.168.1.201:27019');

# 主节点插入数据
>use test
>db.user.insert({uid:1,name:'lily'});

#连接secondary查询同步情况
./bin/mongo --port 27019
>show dbs

rsa:SECONDARY> show dbs;
2015-08-27T11:39:00.638+0800 E QUERY    Error: listDatabases failed:{ "note" : "from execCommand", "ok" : 0, "errmsg" : "not master" }

# 还可以通过isMaster()命令来查看信息;
rsa:PRIMARY> db.isMaster();
{
        "setName" : "rsa",
        "setVersion" : 5,
        "ismaster" : true,
        "secondary" : false,
        "hosts" : [
                "192.168.42.168:27018",
                "192.168.42.168:27019",
                "192.168.42.168:27017"
        ],
        "primary" : "192.168.42.168:27018",
        "me" : "192.168.42.168:27018",
        "electionId" : ObjectId("55dea0cffa0c638625a82486"),
        "maxBsonObjectSize" : 16777216,
        "maxMessageSizeBytes" : 48000000,
        "maxWriteBatchSize" : 1000,
        "localTime" : ISODate("2015-08-27T05:49:13.740Z"),
        "maxWireVersion" : 3,
        "minWireVersion" : 0,
        "ok" : 1
}

# 出现上述错误,是因为slave默认不许读写
>rs.slaveOk();
>show dbs; # 执行上面一个语句就可以看到和primary一致的数据,并且可以把读和写分离开来;
```

以上便是一个最简单的复制集架构,其中如果27017的主节点崩溃,那27018的节点就由从节点变为主节点;注意,如果再添加原来的27017节点进来,那主节点还是27018;
### 自动化配置脚本

```LANG
#!/bin/bash
IP='192.168.1.202'
NA='rsb'

if [ "$1" = "reset" ]
then
  pkill -9 mongo
  rm -rf /home/m*
  exit
fi


if [ "$1" = "install" ]
then

mkdir -p /home/m0 /home/m1 /home/m2 /home/mlog

/usr/local/mongodb/bin/mongod --dbpath /home/m0 --logpath /home/mlog/m17.log --logappend --port 27017 --fork 
--replSet ${NA}
/usr/local/mongodb/bin/mongod --dbpath /home/m1 --logpath /home/mlog/m18.log --logappend --port 27018 --fork 
--replSet ${NA}
/usr/local/mongodb/bin/mongod --dbpath /home/m2 --logpath /home/mlog/m19.log --logappend --port 27019 --fork 
--replSet ${NA}
   
exit
fi

if [ "$1" = "repl" ]
then
/usr/local/mongodb/bin/mongo <<EOF

use admin
rsconf = {
  _id:'${NA}',
  members:[
    {_id:0,host:'${IP}:27017'},
    {_id:1,host:'${IP}:27018'},
    {_id:2,host:'${IP}:27019'},
  ]
}
rs.initiate(rsconf)
EOF
fi
```

[0]: http://docs.mongodb.org/manual/_images/replica-set-read-write-operations-primary.png
[1]: http://docs.mongoing.com/manual-zh/_images/replica-set-primary-with-two-secondaries.png
[2]: http://docs.mongoing.com/manual-zh/_images/replica-set-trigger-election.png
[3]: http://docs.mongoing.com/manual-zh/_images/replica-set-primary-with-secondary-and-arbiter.png
[4]: http://docs.mongoing.com/manual-zh/_images/replica-set-w-arbiter-trigger-election.png
[5]: http://docs.mongoing.com/manual-zh/_images/replica-set-three-members-geographically-distributed.png
[6]: http://docs.mongoing.com/manual-zh/_images/replica-set-hidden-member.png