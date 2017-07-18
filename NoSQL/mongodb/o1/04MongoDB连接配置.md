# [MongoDB学习笔记——MongoDB 连接配置][0] 


**MongoDB 连接标准格式：**

     mongodb ://[username:password@]host1[:port1][,host2[:port2],...[,hostN[:portN]]][/[database][?options]]

 **参数说明** 

 -|-
 -|-
 Mongodb:// |  必填的前缀，标识当前字符串为便准链接格式
 username:password@ |  可选项，给出用户名和密码后，在连接数据库服务器后，驱动都会尝试登陆这个数据库
 host |  uri里唯一的必填项，数据库的连接地址,人如果需要连接副本集，需要制定多个主机地址
 :port |  可选项，如果不填则默认为27017端口
 /database |  希望连接到的数据库名称，只要在设置username:password@后才会有效，如果不指定，则默认为admin数据库
 ?options |  可选项，如果不适用/database,则需要在前面加上/。所有连接选项都是键值对name=value格式，键值对之间使用&或;（分号）分割

### options参数说明  

 -|-
 -|-
 connect=direct &#124; replicaset |  direct: 直接建立一个到服务器的连接。如果指定了多个host，将按先后顺序挨个尝试建立连接，直到连接建立成功为止。如果只指定了一个host，则 direct 为默认值。<br/> replicaset: 使用creplica set semantics建立连接（即使只提供了一个host）。指定的host作为种子列表来查找完整的replica set。当指定多个host时 replicaset 为默认值。
 replicaset=name |  驱动验证建立连接的replica set的名字。应用于 connect=replicaset。
 slaveok=true  &#124; false |  true: 对于 connect=direct 模式，驱动对列表中的第一个服务器建立连接，即使它不是主服务器。对 connect=replicaset 模式，驱动将所有写操作发送到主节点，将所有读操作按round robin顺序分发到从节点。 <br/>  false: 对 connect=direct 模式，驱动按顺序尝试所有host直到找到主节点。对 connect=replicaset 模式，驱动将只连接到主节点，并将所有读操作和写操作都发送到主节点。
 safe=true &#124; false |  true: 驱动在每次更新操作后都发送 getlasterror 命令以确保更新成功（参考 w 和 wtimeout）。  <br/>  false: 驱动每次更新操作后不发送 getlasterror 命令。
 w=n |  **w**：代表server的数量：。   <br/>  w=-1 不等待，不做异常检查 <br/>  w=0 不等待，只返回网络错误 <br/>  w=1 检查本机，并检查网络错误 <br/>  w>1 检查w个server，并返回网络错误 <br/>  应用于safe=true
 wtimeoutMS=ms |  写操作超时的时间，应用于 safe=true.
 fsync=true &#124; false |  是不是等待刷新数据到磁盘，应用于safe=true
 journal=true &#124; false |  是不是等待提交的数据已经写入到日志，并刷新到磁盘，应用于safe=true
 maxPoolSize=n  <br/>  minPoolSize=n |  一些驱动会把没用的连接关闭。 然而,如果连接数低于minPoolSize值之下， 它们不会关闭空闲的连接。注意：连接会按照需要进行创建，因此当连接池被许多连接预填充的时候，minPoolSize不会生效。
 waitQueueTimeoutMS=ms |  在超时之前，线程等待连接生效的总时间。如果连接池到达最大并且所有的连接都在使用，这个参数就生效了。
 waitQueueMultiple=n |  驱动强行限制线程同时等待连接的个数。 这个限制了连接池的倍数。
 connectTimeoutMS=ms |  可以打开连接的时间。
 socketTimeoutMS=ms |  发送和接受sockets的时间
 ReadPreference |  primary <br/>  主节点，默认模式，读操作只在主节点，如果主节点不可用，报错或者抛出异常。 <br/>  primaryPreferred <br/>  首选主节点，大多情况下读操作在主节点，如果主节点不可用，如故障转移，读操作在从节点。 <br/>  secondary <br/>  从节点，读操作只在从节点， 如果从节点不可用，报错或者抛出异常。 <br/>  secondaryPreferred <br/>  首选从节点，大多情况下读操作在从节点，特殊情况（如单主节点架构）读操作在主节点。 <br/>  nearest <br/>  最邻近节点，读操作在最邻近的成员，可能是主节点或者从节点



 **参考示例：**

 连接本地数据库服务器，端口是默认的。

     mongodb://localhost

 使用用户名 fred ，密码 foobar 登录 localhost 的 admin 数据库。

     mongodb://fred:foobar@localhost

 使用用户名 fred ，密码 foobar 登录 localhost 的 baz 数据库。

     mongodb://fred:foobar@localhost/baz

 连接 replica pair, 服务器 1 为 example1.com 服务器 2 为 example2 。

     mongodb://example1.com:27017,example2.com:27017

 连接 replica set 三台服务器 ( 端口 27017, 27018, 和 27019):

    mongodb://localhost,localhost:27018,localhost:27019

 连接 replica set 三台服务器 , 写入操作应用在主服务器  并且分布查询到从服务器。

     mongodb://host1,host2,host3/?slaveOk=true

 直接连接第一个服务器，无论是 replica set 一部分或者主服务器或者从服务器。

     mongodb://host1,host2,host3/?connect=direct;slaveOk=true

 当你的连接服务器有优先级，还需要列出所有服务器，你可以使用上述连接方式。

 安全模式连接到 localhost:

     mongodb://localhost/?safe=true

 以安全模式连接到 replica set ，并且等待至少两个复制服务器成功写入，超时时间设置为 2 秒。

     mongodb://host1,host2,host3/?safe=true;w=2;wtimeoutMS=2000

[0]: http://www.cnblogs.com/AlvinLee/p/6062167.html


